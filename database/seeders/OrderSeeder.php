<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\PriceCalculator;
use Carbon\Carbon;
use Database\Seeders\Support\MockData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderSeeder extends Seeder
{
    private const ORDER_COUNT = 40;

    private const NOTES = [
        'برجاء الاتصال قبل التوصيل',
        'التوصيل بعد الساعة 5 مساءً',
        'يرجى تغليف الطلب كهدية',
        'الرجاء عدم الاتصال قبل 10 صباحًا',
    ];

    public function __construct(private PriceCalculator $calculator)
    {
    }

    public function run(): void
    {
        $products = Product::where('status', true)->where('stock', '>', 0)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No purchasable products found, skipping orders.');

            return;
        }

        $customers = MockData::customers()->load('addresses');
        $coupons = Coupon::all();
        $admins = User::where('role', 'admin')->get();

        // Spread over the last 60 days, keeping a few from the last 48 hours so
        // the dashboard has pending orders and unread notifications.
        $dates = collect(range(1, self::ORDER_COUNT))
            ->map(fn (int $n) => $n <= 5
                ? now()->subMinutes(random_int(10, 48 * 60))
                : now()->subMinutes(random_int(3 * 24 * 60, 60 * 24 * 60)))
            ->sort();

        foreach ($dates as $createdAt) {
            $customer = $customers->isNotEmpty() && fake()->boolean(75) ? $customers->random() : null;
            $lines = $this->buildLines($products);

            if ($lines->isEmpty()) {
                break;
            }

            $order = $this->createOrder($customer, $lines, $coupons, $createdAt);

            if ($admins->isNotEmpty() && $createdAt->gt(now()->subDays(14))) {
                $this->notifyAdmins($admins, $order, $createdAt);
            }
        }
    }

    /**
     * Pick 1-3 in-stock products and price them like checkout does.
     */
    private function buildLines(Collection $products): Collection
    {
        $available = $products->filter(fn (Product $product) => $product->stock > 0);

        if ($available->isEmpty()) {
            return collect();
        }

        return $available->random(min(random_int(1, 3), $available->count()))->map(function (Product $product) {
            $bundleQuantities = $product->bundleOffers()->where('is_active', true)->pluck('quantity');

            $quantity = $bundleQuantities->isNotEmpty() && fake()->boolean(35)
                ? $bundleQuantities->random()
                : random_int(1, 2);

            if ($quantity > $product->stock) {
                $quantity = 1;
            }

            return ['product' => $product, 'quantity' => $quantity] + $this->calculator->calculate($product, $quantity);
        });
    }

    private function createOrder(?User $customer, Collection $lines, Collection $coupons, Carbon $createdAt): Order
    {
        $status = $this->statusFor($createdAt);
        $subtotal = $lines->sum('total_price');

        $shippingCost = $lines->sum(fn (array $line) => $line['product']->shipping_cost * $line['quantity']);
        $shippingCost = $shippingCost > 0 ? $shippingCost : 3000;

        $coupon = fake()->boolean(25) ? $this->pickCoupon($coupons, $customer, $subtotal) : null;
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;

        $address = $customer?->addresses->firstWhere('is_default', true);
        [$state, $city] = $address ? [$address->state, $address->city] : MockData::location();

        return DB::transaction(function () use ($customer, $lines, $coupon, $createdAt, $status, $subtotal, $shippingCost, $discount, $address, $state, $city) {
            $order = new Order([
                'user_id' => $customer?->id,
                'full_name' => $customer?->name ?? fake('ar_EG')->name(),
                'phone_number' => $address?->phone_number ?? $customer?->phone_number ?? MockData::phone(),
                'phone_number_backup' => fake()->boolean(30) ? MockData::phone() : null,
                'city' => $city,
                'state' => $state,
                'shipping_way' => fake()->randomElement(['home', 'home', 'home', 'office', 'pickup']),
                'address_line' => $address?->address_line_1 ?? MockData::street(),
                'status' => $status->value,
                'payment_method' => fake()->randomElement([
                    PaymentMethod::CASH, PaymentMethod::CASH, PaymentMethod::CASH,
                    PaymentMethod::CARD, PaymentMethod::WALLET, PaymentMethod::VALU,
                ])->value,
                'notes' => fake()->boolean(30) ? Arr::random(self::NOTES) : null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discount,
                'total_amount' => max(0, $subtotal - $discount) + $shippingCost,
                'coupon_code' => $coupon?->code,
            ]);
            $order->created_at = $createdAt;
            $order->updated_at = $status === OrderStatus::PENDING ? $createdAt : $createdAt->copy()->addDays(random_int(1, 4))->min(now());
            $order->save();

            foreach ($lines as $line) {
                $item = $order->items()->make([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['final_unit_price'],
                    'total' => $line['total_price'],
                ]);
                $item->created_at = $item->updated_at = $createdAt;
                $item->save();

                if ($status !== OrderStatus::CANCELLED) {
                    $line['product']->decrement('stock', $line['quantity']);
                }
            }

            if ($coupon) {
                $coupon->increment('times_used');

                if ($customer) {
                    $coupon->users()->attach($customer->id, ['order_id' => $order->id]);
                }
            }

            return $order;
        });
    }

    private function pickCoupon(Collection $coupons, ?User $customer, int $subtotal): ?Coupon
    {
        return $coupons
            ->filter(fn (Coupon $coupon) => $coupon->isValid($subtotal))
            ->reject(fn (Coupon $coupon) => $customer && $coupon->hasBeenUsedByUser($customer->id))
            ->shuffle()
            ->first();
    }

    /**
     * Older orders are further along the fulfilment pipeline.
     */
    private function statusFor(Carbon $createdAt): OrderStatus
    {
        if (fake()->boolean(10)) {
            return OrderStatus::CANCELLED;
        }

        $age = (int) $createdAt->diffInDays(now());

        return match (true) {
            $age < 2 => OrderStatus::PENDING,
            $age < 5 => Arr::random([OrderStatus::PENDING, OrderStatus::PROCESSING]),
            $age < 10 => Arr::random([OrderStatus::PROCESSING, OrderStatus::SHIPPED]),
            default => Arr::random([OrderStatus::SHIPPED, OrderStatus::DELIVERED, OrderStatus::DELIVERED]),
        };
    }

    private function notifyAdmins(Collection $admins, Order $order, Carbon $createdAt): void
    {
        Notification::send($admins, new NewOrderNotification($order));

        DB::table('notifications')
            ->where('type', NewOrderNotification::class)
            ->where('data->order_id', $order->id)
            ->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'read_at' => $createdAt->lt(now()->subDays(3)) ? $createdAt->copy()->addHours(2) : null,
            ]);
    }
}
