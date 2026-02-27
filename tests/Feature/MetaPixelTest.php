<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaPixelTest extends TestCase
{
    // Note: Skipping RefreshDatabase as the local database connection is failing. 
    // We will focus on testing the controllers and service in a way that doesn't 
    // strictly require a DB if possible, or assume the user will fix the DB.
    // However, Laravel controllers usually need the DB.
    
    public function test_meta_service_sends_view_content()
    {
        Http::fake();

        $product = new Product([
            'id' => 1,
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $service = new \App\Services\MetaService();
        $service->sendViewContent($product);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'graph.facebook.com') &&
                   $request['data'][0]['event_name'] === 'ViewContent';
        });
    }

    public function test_meta_service_sends_purchase()
    {
        Http::fake();

        $order = new Order([
            'id' => 123,
            'total_amount' => 500,
        ]);
        // Mocking total since it might be accessed as $order->total in MetaService.php
        $order->total = 500; 

        $service = new \App\Services\MetaService();
        $service->sendPurchase($order);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'graph.facebook.com') &&
                   $request['data'][0]['event_name'] === 'Purchase' &&
                   $request['data'][0]['custom_data']['value'] == 500;
        });
    }
}
