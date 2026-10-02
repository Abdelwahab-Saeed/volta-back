<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The storefront only shows and sells visible products: switched on, not deleted, in a switched-on category
 * that is not deleted (Product::visible() / isSellable()).
 */
class StorefrontVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(); // product views, cart and checkout report to Meta
    }

    // ── helpers ────────────────────────────────────────────────────────

    private function product(string $name, array $extra = []): Product
    {
        return Product::factory()->create(array_merge([
            'category_id' => Category::firstOrCreate(['name_ar' => 'قسم', 'name_en' => 'Category'])->id,
            'name_ar' => $name, 'name_en' => $name,
            'price' => 10000, 'stock' => 50, 'status' => true,
        ], $extra));
    }

    /**
     * One product per way of being hidden, keyed by the reason.
     *
     * @return array<string, Product>
     */
    private function hiddenProducts(): array
    {
        $offCategory = Category::factory()->create(['status' => false]);
        $deletedCategory = Category::factory()->create();

        $hidden = [
            'switched off' => $this->product('Lamp off', ['status' => false]),
            'deleted' => $this->product('Lamp deleted'),
            'category switched off' => $this->product('Lamp off category', ['category_id' => $offCategory->id]),
            'category deleted' => $this->product('Lamp deleted category', ['category_id' => $deletedCategory->id]),
        ];

        $hidden['deleted']->delete();
        $deletedCategory->delete();

        return $hidden;
    }

    private function customer(): array
    {
        return [
            'full_name' => 'Customer', 'phone_number' => '01000000000', 'city' => 'Cairo', 'state' => 'Cairo',
            'shipping_way' => 'home', 'payment_method' => 'cash',
        ];
    }

    private function listedIds(string $url, string $key = 'data.items'): array
    {
        return collect($this->getJson($url)->assertOk()->json($key))->pluck('id')->all();
    }

    // ── products and categories ────────────────────────────────────────

    public function test_hidden_products_never_reach_the_product_endpoints(): void
    {
        $visible = $this->product('Lamp visible');
        $hidden = $this->hiddenProducts();

        $this->assertSame([$visible->id], $this->listedIds('/api/products?limit=50'));
        $this->assertSame([$visible->id], $this->listedIds('/api/products?search=Lamp'));
        $this->assertSame([$visible->id], $this->listedIds('/api/products/best-selling?limit=50', 'data'));

        $this->getJson("/api/products/{$visible->id}")->assertOk();
        $this->assertTrue($visible->isSellable());

        foreach ($hidden as $reason => $product) {
            $this->getJson("/api/products/{$product->id}")->assertNotFound();
            $this->assertFalse(Product::withTrashed()->find($product->id)->isSellable(), $reason);
        }
    }

    public function test_a_hidden_product_comes_back_when_switched_on_again(): void
    {
        $product = $this->product('Lamp', ['status' => false]);
        $this->assertSame([], $this->listedIds('/api/products'));

        $product->update(['status' => true]);
        $this->assertSame([$product->id], $this->listedIds('/api/products'));
    }

    public function test_switched_off_and_deleted_categories_are_not_listed(): void
    {
        $on = Category::factory()->create(['status' => true]);
        $off = Category::factory()->create(['status' => false]);
        $deleted = Category::factory()->create();
        $deleted->delete();

        $this->assertSame([$on->id], $this->listedIds('/api/categories', 'data'));
        $this->getJson("/api/categories/{$on->id}")->assertOk();
        $this->getJson("/api/categories/{$off->id}")->assertNotFound();
        $this->getJson("/api/categories/{$deleted->id}")->assertNotFound();
    }

    // ── wishlist and comparison ────────────────────────────────────────

    public function test_wishlist_and_comparison_only_show_and_accept_visible_products(): void
    {
        $user = User::factory()->create();
        $visible = $this->product('Lamp visible');
        $later = $this->product('Lamp hidden later');
        $hidden = $this->hiddenProducts();

        $this->actingAs($user)->postJson('/api/wishlist/toggle', ['product_id' => $visible->id])->assertOk();
        $this->actingAs($user)->postJson('/api/wishlist/toggle', ['product_id' => $later->id])->assertOk();
        $this->actingAs($user)->postJson('/api/comparison', ['product_id' => $visible->id])->assertOk();
        $this->actingAs($user)->postJson('/api/comparison', ['product_id' => $later->id])->assertOk();

        foreach ($hidden as $product) {
            $this->actingAs($user)->postJson('/api/wishlist/toggle', ['product_id' => $product->id])->assertStatus(422);
            $this->actingAs($user)->postJson('/api/comparison', ['product_id' => $product->id])->assertStatus(422);
        }

        $later->update(['status' => false]);

        $this->assertSame([$visible->id], $this->actingAs($user)->getJson('/api/wishlist')->json('data.*.id'));
        $this->assertSame([$visible->id], $this->actingAs($user)->getJson('/api/comparison')->json('data.*.id'));

        // A product that got hidden can still be taken off the wishlist
        $this->actingAs($user)->postJson('/api/wishlist/toggle', ['product_id' => $later->id])->assertOk();
        $this->assertSame([$visible->id], $user->wishlist()->pluck('products.id')->all());
    }

    // ── cart ───────────────────────────────────────────────────────────

    public function test_cart_refuses_hidden_products_and_stops_showing_them(): void
    {
        $user = User::factory()->create();
        $kept = $this->product('Lamp kept');
        $later = $this->product('Lamp hidden later');

        foreach ($this->hiddenProducts() as $product) {
            $this->actingAs($user)->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1])->assertStatus(422);
        }
        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $kept->id, 'quantity' => 1])->assertOk();
        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $later->id, 'quantity' => 2])->assertOk();
        $line = $user->cart->items()->where('product_id', $later->id)->first();

        $later->update(['status' => false]);

        $this->assertSame([$kept->id], $this->actingAs($user)->getJson('/api/cart')->json('data.items.*.product_id'));
        $this->actingAs($user)->putJson("/api/cart/{$line->id}", ['quantity' => 5])->assertStatus(422);
        $this->assertSame(2, (int) $line->fresh()->quantity);

        // The line is only hidden, so it is back as soon as the product is
        $later->update(['status' => true]);
        $this->assertCount(2, $this->actingAs($user)->getJson('/api/cart')->json('data.items'));
    }

    // ── checkout ───────────────────────────────────────────────────────

    public function test_member_checkout_charges_only_what_the_cart_shows(): void
    {
        $user = User::factory()->create();
        $kept = $this->product('Lamp kept');
        $later = $this->product('Lamp hidden later');

        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $kept->id, 'quantity' => 1])->assertOk();
        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $later->id, 'quantity' => 3])->assertOk();
        $later->delete();

        $this->actingAs($user)->postJson('/api/checkout', $this->customer())
            ->assertCreated()
            ->assertJsonPath('data.total_amount', 130) // 100 + 30 shipping: the deleted product is not charged
            ->assertJsonCount(1, 'data.items');

        $this->assertSame(50, (int) Product::withTrashed()->find($later->id)->stock);
    }

    public function test_member_checkout_with_only_hidden_products_is_an_empty_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product('Lamp');

        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $product->category->update(['status' => false]);

        $this->actingAs($user)->postJson('/api/checkout', $this->customer())->assertStatus(400);
        $this->assertSame(0, Order::count());
    }

    public function test_guest_checkout_refuses_unavailable_products(): void
    {
        $ok = $this->product('Lamp ok');

        foreach ($this->hiddenProducts() as $reason => $product) {
            $this->postJson('/api/checkout', $this->customer() + ['items' => [
                ['product_id' => $ok->id, 'quantity' => 1],
                ['product_id' => $product->id, 'quantity' => 1],
            ]])
                ->assertStatus(422)
                ->assertJsonPath('errors.code', 'product_unavailable')
                ->assertJsonPath('errors.product_ids', [$product->id]);

            $this->assertSame(0, Order::count(), $reason);
        }

        $this->assertSame(50, (int) $ok->fresh()->stock);
    }
}
