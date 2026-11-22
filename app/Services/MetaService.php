<?php

namespace App\Services;

use App\Support\Money;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side events for the Meta Conversions API.
 *
 * PageView, ViewContent and AddToCart are sent by the browser pixel only. The pixel has a Conversions API
 * Gateway (OpenBridge) set up in Meta that already copies every browser event server side with the same event
 * id; sending them from here as well, with ids the browser never used, made Meta count each one twice.
 */
class MetaService
{
    /**
     * Send events to the Meta Conversions API. Tracking must never break the request that triggered it
     * (a failed call used to turn a successful checkout into a 500 after the order was already saved),
     * so it is skipped when not configured, has a short timeout, and failures are only logged.
     */
    private function send(array $payload): void
    {
        $pixelId = config('services.meta.pixel_id');
        $token = config('services.meta.access_token');
        if (!$pixelId || !$token) {
            return;
        }

        try {
            Http::timeout(3)->post("https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$token}", $payload);
        } catch (\Throwable $e) {
            Log::warning('Meta Conversions API call failed', ['event' => $payload['data'][0]['event_name'] ?? null, 'error' => $e->getMessage()]);
        }
    }
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

    /**
     * The only event sent from the server. event_id is the order id, the same eventID the storefront passes to
     * fbq('track', 'Purchase') after checkout, so Meta keeps one Purchase per order.
     */
    public function sendPurchase($order)
    {
        $this->send([
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
                        "value" => Money::toPounds($order->total_amount),
                        "content_type" => "product",
                        "content_ids" => $order->items->pluck('product_id')->map(fn($id) => (string)$id)->toArray(),
                        "contents" => $order->items->map(function ($item) {
                            return [
                                "id" => (string)$item->product_id,
                                "quantity" => $item->quantity,
                                "item_price" => Money::toPounds($item->price)
                            ];
                        })->toArray(),
                    ]
                ]
            ]
        ]);
    }

    public function sendRegistration($user)
    {
        $this->send([
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

}