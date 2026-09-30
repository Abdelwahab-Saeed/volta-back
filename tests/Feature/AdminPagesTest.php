<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every admin screen renders for an admin (guards the Blade views against typos and missing variables).
 */
class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 2]);

        $routes = [
            route('admin.dashboard'),
            route('admin.notifications.index'),
            route('admin.orders.index'),
            route('admin.reports.sold_products'),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.products.show', $product),
            route('admin.products.edit', $product),
            route('admin.products.features.index', $product),
            route('admin.products.images.index', $product),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $category),
            route('admin.offers.index'),
            route('admin.offers.create'),
            route('admin.coupons.index'),
            route('admin.coupons.create'),
            route('admin.banners.index'),
            route('admin.banners.create'),
            route('admin.posts.index'),
            route('admin.posts.create'),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.edit', $admin),
            route('admin.settings.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        // The dashboard flags the low-stock product
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertSee('قاربت على النفاد')->assertSee($product->name);
    }

    public function test_auth_pages_render(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('تسجيل الدخول');
        $this->get(route('admin.password.request'))->assertOk();
        $this->get(route('admin.password.reset', ['token' => 'abc']))->assertOk();
    }
}
