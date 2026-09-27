<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Prices an offer bought directly from the offer page. The same quote is shown on the page and charged at checkout.
 *
 * What the customer picks:
 *   - sets:       how many times to take the offer (bundle sets, or buy-X-get-Y groups)
 *   - product_id: for buy_x_get_y with several eligible products, which one (not needed when there is only one)
 */
class OfferPricing
{
    public const MAX_SETS = 20;

    public function __construct(private PriceCalculator $prices)
    {
    }

    /**
     * @return array{
     *     offer: Offer, sets: int, product_id: ?int,
     *     lines: Collection, gifts: Collection,
     *     subtotal: int, discount: int, shipping: int, total: int,
     *     issues: array<int, array{code: string, message: string}>, purchasable: bool
     * }
     *
     * @throws ValidationException when the selection itself is invalid
     */
    public function quote(Offer $offer, int $sets, ?int $productId = null): array
    {
        if ($sets < 1 || $sets > self::MAX_SETS) {
            throw ValidationException::withMessages(['sets' => 'عدد مرات العرض يجب أن يكون بين 1 و ' . self::MAX_SETS . '.']);
        }

        // Deleted products are loaded too, so a bundle never silently loses one of its products.
        $offerProducts = $offer->products()->withTrashed()->get();
        $offer->setRelation('products', $offerProducts);

        $issues = [];
        $lines = collect();

        if ($offer->type === 'bundle') {
            $productId = null;
            foreach ($offerProducts as $product) {
                $lines->push($this->line($product, max(1, (int) $product->pivot->quantity) * $sets));
            }
        } else {
            $product = $this->chosenProduct($offerProducts, $productId);
            $productId = $product->id;
            $unitsPerSet = $offer->get_product_id ? $offer->buy_quantity : $offer->buy_quantity + $offer->get_quantity;
            $lines->push($this->line($product, $unitsPerSet * $sets));
        }

        foreach ($lines as $line) {
            if (!$this->isSellable($line->product)) {
                $issues[] = ['code' => 'product_unavailable', 'message' => "المنتج {$line->product->name} غير متاح حالياً"];
            }
        }

        $subtotal = (int) $lines->sum('total_line_price');
        $result = $offer->calculateDiscount($lines, $subtotal);

        $gifts = collect($result['free_items'])->map(function (array $free) use (&$issues) {
            $gift = Product::withTrashed()->find($free['product_id']);
            if (!$gift || !$this->isSellable($gift)) {
                $issues[] = ['code' => 'gift_unavailable', 'message' => 'منتج الهدية الخاص بالعرض غير متاح حالياً'];
            }

            return (object) ['product' => $gift, 'product_id' => $free['product_id'], 'quantity' => $free['quantity']];
        });

        // A bundle can end up no cheaper than buying the products separately if their prices dropped after it was set.
        if ($result['discount'] <= 0 && $gifts->isEmpty()) {
            $issues[] = ['code' => 'no_saving', 'message' => 'العرض غير متاح حالياً'];
        }

        foreach ($this->stockShortages($lines, $gifts) as $product) {
            $issues[] = ['code' => 'out_of_stock', 'message' => "الكمية المطلوبة من {$product->name} غير متوفرة حالياً"];
        }

        $shipping = $this->prices->shippingCost($lines);

        return [
            'offer' => $offer,
            'sets' => $sets,
            'product_id' => $productId,
            'lines' => $lines,
            'gifts' => $gifts,
            'subtotal' => $subtotal,
            'discount' => $result['discount'],
            'shipping' => $shipping,
            'total' => $subtotal - $result['discount'] + $shipping,
            'issues' => $issues,
            'purchasable' => $issues === [],
        ];
    }

    private function line(Product $product, int $quantity): object
    {
        $calculation = $this->prices->calculate($product, $quantity);

        return (object) [
            'product' => $product,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_snapshot' => $calculation['final_unit_price'],
            'total_line_price' => $calculation['total_price'],
        ];
    }

    private function chosenProduct(Collection $offerProducts, ?int $productId): Product
    {
        if ($productId === null && $offerProducts->count() === 1) {
            return $offerProducts->first();
        }

        $product = $productId ? $offerProducts->firstWhere('id', $productId) : null;
        if (!$product) {
            throw ValidationException::withMessages(['product_id' => 'اختر منتجاً من منتجات هذا العرض.']);
        }

        return $product;
    }

    private function isSellable(Product $product): bool
    {
        return !$product->trashed() && (bool) $product->status;
    }

    /**
     * Products whose stock does not cover paid + gift units together.
     */
    private function stockShortages(Collection $lines, Collection $gifts): Collection
    {
        return $lines->concat($gifts->filter(fn ($gift) => $gift->product))
            ->groupBy('product_id')
            ->filter(fn (Collection $group) => $group->first()->product->stock < $group->sum('quantity'))
            ->map(fn (Collection $group) => $group->first()->product)
            ->values();
    }
}
