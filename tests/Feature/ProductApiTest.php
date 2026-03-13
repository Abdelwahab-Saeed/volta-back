<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_api_returns_preview_url_and_relationships()
    {
        $category = Category::factory()->create(['name' => 'Phones']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'iPhone 15',
            'preview_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        ProductFeature::create([
            'product_id' => $product->id,
            'name' => 'A17 Bionic chip',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image' => 'uploads/products/extra1.jpg',
        ]);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.preview_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->assertJsonCount(1, 'data.features')
            ->assertJsonPath('data.features.0.name', 'A17 Bionic chip')
            ->assertJsonCount(1, 'data.extra_images')
            ->assertJsonPath('data.extra_images.0.image', 'uploads/products/extra1.jpg');
    }
}
