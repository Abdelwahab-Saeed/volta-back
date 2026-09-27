<?php

namespace App\Services;

use App\Models\Product;

class PriceCalculator
{
    /**
     * Default shipping (30 EGP) when none of the products has its own shipping cost.
     */
    public const DEFAULT_SHIPPING = 3000;

    /**
     * Calculate price for a product based on quantity.
     * All amounts are integer piasters. Quantity never changes the unit price: package deals are offers,
     * bought from the offer page.
     *
     * @param Product $product
     * @param int $quantity
     * @return array
     */
    public function calculate(Product $product, int $quantity)
    {
        $basePrice = $product->final_price;
        $originalPrice = $product->price;

        $discountInfo = null;

        // Product's own discount, if any
        if ($product->discount > 0) {
            $discountInfo = [
                'type' => 'product_discount',
                'name' => "خصم منتج (" . ($product->discount * 1) . "%)",
                'percentage' => $product->discount,
                'amount_per_unit' => $originalPrice - $basePrice,
            ];
        }

        return [
            'original_unit_price' => $originalPrice,
            'base_unit_price' => $basePrice,
            'final_unit_price' => $basePrice,
            'quantity' => $quantity,
            'total_price' => $basePrice * $quantity,
            'discount_applied' => $discountInfo
        ];
    }

    /**
     * Shipping for paid lines (each needs product and quantity): each product's shipping cost × quantity,
     * or the default when that comes to zero. Offer gift lines are not passed in, so they ship free.
     */
    public function shippingCost(iterable $lines): int
    {
        $shipping = 0;
        foreach ($lines as $line) {
            $shipping += ($line->product->shipping_cost ?? 0) * $line->quantity;
        }

        return $shipping > 0 ? $shipping : self::DEFAULT_SHIPPING;
    }
}
