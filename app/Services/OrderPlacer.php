<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Creates an order from already-priced lines. Shared by the cart checkout and the offer checkout,
 * so stock, notifications and tracking behave the same for both.
 */
class OrderPlacer
{
    public function __construct(private MetaService $metaService)
    {
    }

    /**
     * An order already placed with this idempotency key (a retried or double-submitted checkout).
     */
    public function findByIdempotencyKey(?string $key): ?Order
    {
        return $key ? Order::where('idempotency_key', $key)->first() : null;
    }

    /**
     * @param  array       $attributes  Order columns (customer, amounts, coupon/offer fields, idempotency_key)
     * @param  Collection  $lines       Paid lines: product, product_id, quantity, price_snapshot, total_line_price (piasters)
     * @param  Collection  $gifts       Free offer gift lines: product, product_id, quantity
     * @param  callable|null $inTransaction  Extra writes that must commit with the order (coupon usage, clearing the cart)
     *
     * @throws InsufficientStockException
     */
    public function place(array $attributes, Collection $lines, Collection $gifts, ?callable $inTransaction = null): Order
    {
        try {
            $order = DB::transaction(function () use ($attributes, $lines, $gifts, $inTransaction) {
                $this->reserveStock($lines, $gifts);

                $order = Order::create($attributes + ['status' => OrderStatus::PENDING->value]);

                foreach ($lines as $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $line->product_id,
                        'quantity' => $line->quantity,
                        'price' => $line->price_snapshot,
                        'total' => $line->total_line_price,
                    ]);
                }

                // Offer gifts are their own order lines at price 0, so the warehouse ships them
                foreach ($gifts as $gift) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $gift->product_id,
                        'quantity' => $gift->quantity,
                        'price' => 0,
                        'total' => 0,
                    ]);
                }

                if ($inTransaction) {
                    $inTransaction($order);
                }

                return $order;
            });
        } catch (UniqueConstraintViolationException $e) {
            // Two requests with the same idempotency key raced; the other one created the order.
            $existing = $this->findByIdempotencyKey($attributes['idempotency_key'] ?? null);
            if ($existing) {
                return $existing;
            }
            throw $e;
        }

        $admins = User::where('role', 'admin')->get();
        if ($admins->count() > 0) {
            Notification::send($admins, new NewOrderNotification($order));
        }

        $this->metaService->sendPurchase($order);

        return $order;
    }

    /**
     * Lock each product row, check stock for paid + gift units together, then decrement.
     */
    private function reserveStock(Collection $lines, Collection $gifts): void
    {
        $needed = $lines->concat($gifts)
            ->groupBy('product_id')
            ->map(fn (Collection $group) => (int) $group->sum('quantity'));

        $products = Product::withTrashed()->whereIn('id', $needed->keys())->lockForUpdate()->get()->keyBy('id');

        foreach ($needed as $productId => $quantity) {
            $product = $products[$productId];
            if ($product->stock < $quantity) {
                throw new InsufficientStockException($product, (int) $product->stock, $quantity);
            }
        }

        foreach ($needed as $productId => $quantity) {
            $products[$productId]->decrement('stock', $quantity);
        }
    }
}
