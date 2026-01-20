<?php

namespace App\Http\Resources;

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
            'description' => $this->description,
            'price' => (float) $this->price,
            'final_price' => (float) $this->final_price,
            'discount' => (float) $this->discount,
            'stock' => (int) $this->stock,
            'image' => $this->image,
            'status' => (bool) $this->status,
            'category' => new CategoryResource($this->whenLoaded('category')),
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
