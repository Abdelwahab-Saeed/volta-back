<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Cart;
use App\Models\CartItem;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_item_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    public function test_user_can_increment_item_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3 
        ]);
    }

    public function test_user_can_get_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['id', 'user_id', 'items']]);
    }

    public function test_user_can_update_item_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $cart = $user->cart;
        $cartItem = $cart->items()->first();

        $response = $this->actingAs($user)->putJson('/api/cart/' . $cartItem->id, [
            'quantity' => 3
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 3
        ]);
    }

    public function test_user_can_remove_item_from_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cart = $user->cart;
        $cartItem = $cart->items()->first();

        $response = $this->actingAs($user)->deleteJson('/api/cart/' . $cartItem->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_user_can_clear_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/cart/clear');

        $response->assertStatus(200);
        $this->assertDatabaseCount('cart_items', 0);
    }
}
