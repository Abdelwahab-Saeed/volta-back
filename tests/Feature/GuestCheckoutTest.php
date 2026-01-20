<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest checkout flow.
     */
    public function test_guest_can_checkout_with_items_in_request(): void
    {
        // Create a category and product
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test Description',
            'price' => 100.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $data = [
            'full_name' => 'Guest User',
            'phone_number' => '1234567890',
            'phone_number_backup' => '0987654321',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'address_line' => '123 Main St',
            'shipping_way' => 'home',
            'payment_method' => 'cash',
            'notes' => 'Test Note',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ];

        $response = $this->postJson('/api/checkout', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.full_name', 'Guest User')
            ->assertJsonPath('data.total_amount', 230.00); // (100 * 2) + 30 shipping

        // Verify order in database
        $this->assertDatabaseHas('orders', [
            'full_name' => 'Guest User',
            'user_id' => null,
            'subtotal' => 200.00,
            'address_line' => '123 Main St',
        ]);

        // Verify stock deduction
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);
        
        // Verify order item created
        $order = Order::where('full_name', 'Guest User')->first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100.00,
        ]);
    }
    
    /**
     * Test invalid guest checkout request (missing items).
     */
    public function test_guest_cannot_checkout_without_items(): void
    {
        $data = [
            'full_name' => 'Guest User',
            'phone_number' => '1234567890',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'shipping_way' => 'home',
            'payment_method' => 'cash',
            // Missing items
        ];

        $response = $this->postJson('/api/checkout', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }
}
