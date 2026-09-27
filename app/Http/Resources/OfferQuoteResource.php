<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An OfferPricing quote as the offer page shows it (amounts in pounds, like the rest of the API).
 */
class OfferQuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $quote = $this->resource;

        return [
            'offer_id' => $quote['offer']->id,
            'sets' => $quote['sets'],
            'product_id' => $quote['product_id'],
            'items' => $quote['lines']->map(fn ($line) => [
                'product_id' => $line->product_id,
                'name' => $line->product->name,
                'image' => $line->product->image,
                'quantity' => $line->quantity,
                'unit_price' => Money::toPounds($line->price_snapshot),
                'total' => Money::toPounds($line->total_line_price),
            ])->values(),
            'gifts' => $quote['gifts']->map(fn ($gift) => [
                'product_id' => $gift->product_id,
                'name' => $gift->product?->name,
                'image' => $gift->product?->image,
                'quantity' => $gift->quantity,
            ])->values(),
            'subtotal' => Money::toPounds($quote['subtotal']),
            'discount' => Money::toPounds($quote['discount']),
            'shipping_cost' => Money::toPounds($quote['shipping']),
            'total' => Money::toPounds($quote['total']),
            'purchasable' => $quote['purchasable'],
            'issues' => $quote['issues'],
        ];
    }
}
