<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'price' => (float) Money::toPounds($this->price),
            'final_price' => (float) Money::toPounds($this->final_price),
            'discount' => (float) $this->discount,
            'discount_price' => (float) Money::toPounds($this->discount_price),
            'shipping_cost' => (float) Money::toPounds($this->shipping_cost),
            'stock' => (int) $this->stock,
            'image' => $this->image,
            'preview_url' => $this->preview_url,
            'status' => (bool) $this->status,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'features' => ProductFeatureResource::collection($this->whenLoaded('features')),
            'extra_images' => ProductImageResource::collection($this->whenLoaded('extraImages')),
            'bundle_offers' => $this->whenLoaded('bundleOffers', function() {
                // If bundle offers need translation later, we'd make a resource for them too.
                // For now, they are just numbers.
                return $this->bundleOffers;
            }),
            'wishlisted' => $this->when(auth('sanctum')->check(), function() {
                 return $this->wishlistedBy()->where('user_id', auth('sanctum')->id())->exists();
            }, false),
        ];
    }
}
