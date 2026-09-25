<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'phone_number_backup' => $this->phone_number_backup,
            'city' => $this->city,
            'state' => $this->state,
            'shipping_way' => $this->shipping_way,
            'address_line' => $this->address_line,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'subtotal' => (float) Money::toPounds($this->subtotal),
            'shipping_cost' => (float) Money::toPounds($this->shipping_cost),
            'discount_amount' => (float) Money::toPounds($this->discount_amount),
            'total_amount' => (float) Money::toPounds($this->total_amount),
            'coupon_code' => $this->coupon_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
