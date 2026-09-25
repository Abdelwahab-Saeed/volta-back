<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_checkout_successfully()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000]);
        $address = Address::factory()->create(['user_id' => $user->id]);
        
        // Setup Cart
        $cart = Cart::create(['user_id' => $user->id]);
        // CartItem created via API below to avoid duplication issues
        
        $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->postJson('/api/checkout', [
            'address_id' => $address->id,
            'payment_method' => 'cash',
            'notes' => 'Leave at door',
        ]);

        $response->assertStatus(201);
        
        // Total = (100 * 2) + 30 (shipping) = 230
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 23000,
            'shipping_cost' => 3000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'notes' => 'Leave at door',
        ]);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_with_coupon()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000]);
        $address = Address::factory()->create(['user_id' => $user->id]);
        $coupon = Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10, // 10% off
        ]);

        $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/checkout', [
            'address_id' => $address->id,
            'coupon_code' => 'SAVE10',
            'payment_method' => 'card',
        ]);

        $response->assertStatus(201);
        // Subtotal: 100
        // Discount: 10
        // Shipping: 30
        // Total: 90 + 30 = 120
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'subtotal' => 10000,
            'discount_amount' => 1000,
            'shipping_cost' => 3000,
            'total_amount' => 12000,
            'coupon_code' => 'SAVE10',
            'payment_method' => 'card',
        ]);
        
        $this->assertEquals(1, $coupon->fresh()->times_used);
    }
}
