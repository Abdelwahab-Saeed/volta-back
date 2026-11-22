<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\MetaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The browser pixel sends PageView, ViewContent and AddToCart, and the Conversions API Gateway set up in Meta
 * copies each of them server side with the same event id. The backend used to send its own copy of those three
 * with different ids, so Meta counted them twice. Only Purchase is sent from here, with the order id as event_id
 * so it is deduplicated against the browser Purchase (OfferCheckout.jsx / useCheckoutStore.js use the same id).
 */
class MetaPixelTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_to_cart_sends_nothing_to_meta()
    {
        Http::fake();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1])->assertOk();

        Http::assertNothingSent();
    }

    public function test_viewing_a_product_sends_nothing_to_meta()
    {
        Http::fake();
        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->id}")->assertOk();

        Http::assertNothingSent();
    }

    public function test_page_view_endpoint_sends_nothing_to_meta()
    {
        Http::fake();

        $this->postJson('/api/track/pageview', ['url' => 'https://www.volta-eg.com/']);

        Http::assertNothingSent();
    }

    public function test_checkout_sends_one_purchase_with_the_order_id_as_event_id()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000]);
        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 2])->assertOk();

        Http::fake();
        $this->actingAs($user)->postJson('/api/checkout', [
            'full_name' => 'Test Customer',
            'phone_number' => '01000000000',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'address_line' => '1 Test St',
            'shipping_way' => 'home',
            'payment_method' => 'cash',
        ])->assertCreated();

        $order = Order::sole();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.facebook.com')
            && $request['data'][0]['event_name'] === 'Purchase'
            && $request['data'][0]['event_id'] === (string) $order->id
            && $request['data'][0]['custom_data']['value'] == 230);
    }

    public function test_purchase_value_is_sent_in_pounds()
    {
        Http::fake();
        $order = (new Order())->forceFill(['id' => 123, 'total_amount' => 50000]);

        (new MetaService())->sendPurchase($order);

        Http::assertSent(fn ($request) => $request['data'][0]['event_name'] === 'Purchase'
            && $request['data'][0]['event_id'] === '123'
            && $request['data'][0]['custom_data']['value'] == 500);
    }
}
