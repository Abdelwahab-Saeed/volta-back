<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MetaService 
{
    private function hashData($data)
    {
        return $data ? hash('sha256', strtolower(trim($data))) : null;
    }

    private function getFbc()
    {
        return request()->cookie('_fbc');
    }

    private function getFbp()
    {
        return request()->cookie('_fbp');
    }

    private function getUserData($user = null)
    {
        return [
            "em" => $user ? $this->hashData($user->email) : null,
            "ph" => $user ? $this->hashData($user->phone_number) : null,
            "fbc" => $this->getFbc(),
            "fbp" => $this->getFbp(),
            "client_ip_address" => request()->ip(),
            "client_user_agent" => request()->userAgent(),
        ];
    }

    public function sendViewContent($product)
    {
        Http::post("https://graph.facebook.com/v18.0/" . config('services.meta.pixel_id') . "/events?access_token=" . config('services.meta.access_token'), [
            "data" => [
                [
                    "event_name" => "ViewContent",
                    "event_time" => time(),
                    "action_source" => "website",
                    "event_id" => 'vc_' . $product->id . '_' . time(),
                    "event_source_url" => request()->fullUrl(),
                    "user_data" => $this->getUserData(auth()->user()),
                    "custom_data" => [
                        "content_ids" => [(string)$product->id],
                        "content_type" => "product",
                        "value" => $product->price,
                        "currency" => "EGP"
                    ]
                ]
            ]
        ]);
    }

    public function sendPurchase($order)
    {
        Http::post("https://graph.facebook.com/v18.0/" . config('services.meta.pixel_id') . "/events?access_token=" . config('services.meta.access_token'), [
            "data" => [
                [
                    "event_name" => "Purchase",
                    "event_time" => time(),
                    "action_source" => "website",
                    "event_id" => (string)$order->id,
                    "event_source_url" => url('/checkout/success'),
                    "user_data" => [
                        "em" => $order->user ? $this->hashData($order->user->email) : null,
                        "ph" => $this->hashData($order->phone_number),
                        "fbc" => $this->getFbc(),
                        "fbp" => $this->getFbp(),
                        "client_ip_address" => request()->ip(),
                        "client_user_agent" => request()->userAgent(),
                    ],
                    "custom_data" => [
                        "currency" => "EGP",
                        "value" => $order->total_amount,
                        "content_type" => "product",
                        "content_ids" => $order->items->pluck('product_id')->map(fn($id) => (string)$id)->toArray(),
                        "contents" => $order->items->map(function ($item) {
                            return [
                                "id" => (string)$item->product_id,
                                "quantity" => $item->quantity,
                                "item_price" => $item->price
                            ];
                        })->toArray(),
                    ]
                ]
            ]
        ]);
    }

    public function sendRegistration($user)
    {
        Http::post("https://graph.facebook.com/v18.0/" . config('services.meta.pixel_id') . "/events?access_token=" . config('services.meta.access_token'), [
            "data" => [
                [
                    "event_name" => "CompleteRegistration",
                    "event_time" => time(),
                    "action_source" => "website",
                    "event_source_url" => url('/register'),
                    "user_data" => $this->getUserData($user)
                ]
            ]
        ]);
    }

    public function sendAddToCart($product, $user = null)
    {
        Http::post(
            "https://graph.facebook.com/v18.0/" . config('services.meta.pixel_id') . "/events?access_token=" . config('services.meta.access_token'),
            [
                "data" => [
                    [
                        "event_name" => "AddToCart",
                        "event_time" => time(),
                        "action_source" => "website",
                        "event_source_url" => request()->fullUrl(),
                        "event_id" => 'atc_' . $product->id . '_' . time(),
                        "user_data" => $this->getUserData($user ?: auth()->user()),
                        "custom_data" => [
                            "content_ids" => [(string)$product->id],
                            "content_type" => "product",
                            "value" => $product->price,
                            "currency" => "EGP"
                        ]
                    ]
                ]
            ]
        );
    }

    public function sendAddToWishlist($product, $user = null)
    {
        Http::post(
            "https://graph.facebook.com/v18.0/" . config('services.meta.pixel_id') . "/events?access_token=" . config('services.meta.access_token'),
            [
                "data" => [
                    [
                        "event_name" => "AddToWishlist",
                        "event_time" => time(),
                        "action_source" => "website",
                        "event_source_url" => request()->fullUrl(),
                        "event_id" => 'atw_' . $product->id . '_' . time(),
                        "user_data" => $this->getUserData($user ?: auth()->user()),
                        "custom_data" => [
                            "content_ids" => [(string)$product->id],
                            "content_type" => "product",
                            "value" => $product->price,
                            "currency" => "EGP"
                        ]
                    ]
                ]
            ]
        );
    }
}