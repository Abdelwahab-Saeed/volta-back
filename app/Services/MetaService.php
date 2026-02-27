<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MetaService 
{
    public function sendViewContent($product)
    {
        Http::post("https://graph.facebook.com/v18.0/" . config('services.meta.pixel_id') . "/events?access_token=" . config('services.meta.access_token'), [
            "data" => [
                [
                    "event_name" => "ViewContent",
                    "event_time" => time(),
                    "action_source" => "website",
                    "event_source_url" => request()->fullUrl(),
                    "user_data" => [
                        "client_ip_address" => request()->ip(),
                        "client_user_agent" => request()->userAgent(),
                    ],
                    "custom_data" => [
                        "content_ids" => [$product->id],
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
                    "event_id" => $order->id,
                    "event_source_url" => url('/checkout/success'),
                    "user_data" => [
                        "em" => $order->user ? hash('sha256', strtolower($order->user->email)) : null,
                        "ph" => $order->user ? $order->user->phone : null,
                        "client_ip_address" => request()->ip(),
                        "client_user_agent" => request()->userAgent(),
                    ],
                    "custom_data" => [
                        "currency" => "EGP",
                        "value" => $order->total
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
                    "user_data" => [
                        "em" => hash('sha256', strtolower($user->email)),
                        "ph" => $user ? $user->phone : null,
                        "client_ip_address" => request()->ip(),
                        "client_user_agent" => request()->userAgent(),
                    ]
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
                        "event_id" => uniqid(),

                        "user_data" => [
                            "em" => $user ? hash('sha256', strtolower($user->email)) : null,
                            "ph" => $user ? $user->phone : null,
                            "client_ip_address" => request()->ip(),
                            "client_user_agent" => request()->userAgent(),
                        ],

                        "custom_data" => [
                            "content_ids" => [$product->id],
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
                        "event_id" => uniqid(),

                        "user_data" => [
                            "em" => $user ? hash('sha256', strtolower($user->email)) : null,
                            "ph" => $user ? $user->phone : null,
                            "client_ip_address" => request()->ip(),
                            "client_user_agent" => request()->userAgent(),
                        ],

                        "custom_data" => [
                            "content_ids" => [$product->id],
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