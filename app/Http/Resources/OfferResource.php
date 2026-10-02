<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\OfferPricing;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An offer as the storefront shows it. Texts come localized (Accept-Language) and prices come from OfferPricing,
 * the same code checkout charges with, so the frontend only renders — it never hardcodes offer types or does math.
 *
 * Needs products and freeProduct loaded.
 */
class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pricing = app(OfferPricing::class);
        $requiresChoice = $this->type === 'buy_x_get_y' && $this->products->count() > 1;

        // One quote for a single set; with several eligible products there is one per product instead.
        $quote = $requiresChoice ? null : $pricing->quote($this->resource, 1);
        $productQuotes = $requiresChoice
            ? $this->products->mapWithKeys(fn (Product $p) => [$p->id => $pricing->quote($this->resource, 1, $p->id)])
            : collect();

        $available = $requiresChoice
            ? $productQuotes->contains(fn ($q) => $q['purchasable'])
            : $quote['purchasable'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'display' => [
                'type_label' => \App\Models\Offer::typeLabel($this->type),
                'summary' => $this->summary(),
                // null when the customer must pick a product first: each product then carries its own prices.
                ...($quote ? $this->prices($quote) : ['regular_price' => null, 'offer_price' => null, 'savings' => null]),
            ],
            'purchase' => [
                'requires_product_choice' => $requiresChoice,
                'max_sets' => OfferPricing::MAX_SETS,
                'available' => $available,
                'unavailable_reason' => $available ? null : $this->firstIssue($quote ?? $productQuotes->first()),
            ],
            'products' => $this->products->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image,
                'price' => Money::toPounds($p->final_price),
                'original_price' => Money::toPounds($p->price),
                'quantity' => (int) $p->pivot->quantity, // units per bundle set
                'available' => $p->isSellable(),
                'offer' => $requiresChoice ? $this->prices($productQuotes[$p->id]) + [
                    'available' => $productQuotes[$p->id]['purchasable'],
                ] : null,
            ])->values(),
            'gift' => $this->get_product_id && $this->freeProduct ? [
                'id' => $this->freeProduct->id,
                'name' => $this->freeProduct->name,
                'image' => $this->freeProduct->image,
                'quantity' => $this->get_quantity,
            ] : null,
        ];
    }

    /**
     * Prices for one set, in pounds. A gift counts in the regular price, so "savings" shows its value too.
     */
    private function prices(array $quote): array
    {
        $giftValue = $quote['gifts']->sum(fn ($gift) => ($gift->product?->final_price ?? 0) * $gift->quantity);
        $regular = $quote['subtotal'] + $giftValue;
        $offerPrice = $quote['subtotal'] - $quote['discount'];

        return [
            'regular_price' => Money::toPounds($regular),
            'offer_price' => Money::toPounds($offerPrice),
            'savings' => Money::toPounds($regular - $offerPrice),
        ];
    }

    private function firstIssue(?array $quote): ?string
    {
        return $quote['issues'][0]['message'] ?? null;
    }
}
