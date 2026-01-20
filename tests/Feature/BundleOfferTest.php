<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\ProductBundleOffer;

class BundleOfferTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test exact quantity match triggers bundle price.
     */
    public function test_exact_quantity_match_applies_bundle_price_guest()
    {
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Bundle Product', 
            'price' => 100, 
            'stock' => 50,
            'is_active' => true 
        ]);

        // Bundle: Buy exactly 3 for 250 (instead of 300)
        ProductBundleOffer::create([
            'product_id' => $product->id,
            'quantity' => 3,
            'bundle_price' => 250,
            'is_active' => true
        ]);

        // Request usage of EXACT quantity (3)
        $data = [
            'full_name' => 'Guest',
            'phone_number' => '123',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'shipping_way' => 'home',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3]
            ]
        ];

        $response = $this->postJson('/api/checkout', $data);

        $response->assertStatus(201);
        
        // Price should be 250 (Bundle) + 30 shipping = 280
        $response->assertJsonPath('data.subtotal', 250.00); 
        $response->assertJsonPath('data.total_amount', 280.00);

        $this->assertDatabaseHas('orders', [
            'subtotal' => 250.00,
            'total_amount' => 280.00
        ]);
    }

    /**
     * Test non-match quantity uses standard price.
     */
    public function test_non_matching_quantity_uses_standard_price()
    {
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Bundle Product', 
            'price' => 100, 
            'stock' => 50,
            'is_active' => true 
        ]);

        // Bundle: Buy exactly 3 for 250
        ProductBundleOffer::create([
            'product_id' => $product->id,
            'quantity' => 3,
            'bundle_price' => 250,
            'is_active' => true
        ]);

        // Request quantity 2 (Should not trigger bundle)
        // Price: 100 * 2 = 200 + 30 = 230
        $data = [
            'full_name' => 'Guest',
            'phone_number' => '123',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'shipping_way' => 'home',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ];

        $response = $this->postJson('/api/checkout', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.subtotal', 200.00); 
    }

    /**
     * Test higher quantity does not trigger lower bundle (Exact match only).
     */
    public function test_higher_quantity_does_not_trigger_bundle()
    {
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Bundle Product', 
            'price' => 100, 
            'stock' => 50,
            'is_active' => true 
        ]);

        // Bundle: Buy exactly 3 for 250
        ProductBundleOffer::create([
            'product_id' => $product->id,
            'quantity' => 3,
            'bundle_price' => 250,
            'is_active' => true
        ]);

        // Request quantity 4
        // Logic: Exact match only, so 4 is not 3.
        // Price: 100 * 4 = 400
        
        $data = [
            'full_name' => 'Guest',
            'phone_number' => '123',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'shipping_way' => 'home',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4]
            ]
        ];

        $response = $this->postJson('/api/checkout', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.subtotal', 400.00); 
    }
}
