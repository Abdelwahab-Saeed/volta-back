<?php

namespace App\Services;

use App\Models\Product;

class PriceCalculator
{
    /**
     * Calculate price for a product based on quantity.
     * 
     * @param Product $product
     * @param int $quantity
     * @return array
     */
    public function calculate(Product $product, int $quantity)
    {
        $basePrice = $product->final_price;
        $originalPrice = $product->price;
        
        // Find EXACT match bundle offer
        $bundleOffer = $product->bundleOffers()
            ->where('is_active', true)
            ->where('quantity', $quantity)
            ->first();
            
        $finalUnitPrice = $basePrice;
        $totalPrice = $basePrice * $quantity;
        $discountInfo = null;
        
        if ($bundleOffer) {
            // Apply Fixed Bundle Price
            $totalPrice = $bundleOffer->bundle_price;
            $finalUnitPrice = $totalPrice / $quantity;
            
            $discountInfo = [
                'type' => 'bundle_offer',
                'name' => "عرض خاص ({$quantity} قطع بسعر {$bundleOffer->bundle_price})",
                'min_quantity' => $quantity, // used as exact match
                'bundle_price' => $bundleOffer->bundle_price,
            ];
        } else {
             // Fallback to standard unit price (with product fix discount if any)
             if ($product->discount > 0) {
                 $discountInfo = [
                     'type' => 'product_discount',
                     'name' => "خصم منتج (" . ($product->discount * 1) . "%)",
                     'percentage' => $product->discount,
                     'amount_per_unit' => $originalPrice - $basePrice,
                 ];
             }
        }
        
        return [
            'original_unit_price' => $originalPrice,
            'base_unit_price' => $basePrice, 
            'final_unit_price' => $finalUnitPrice,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'discount_applied' => $discountInfo
        ];
    }
}
