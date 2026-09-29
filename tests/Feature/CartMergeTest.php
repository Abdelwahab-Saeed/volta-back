<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * POST /api/cart/merge: the guest cart (kept in the browser) joins the account cart after login.
 */
class CartMergeTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $pounds, array $extra = []): Product
    {
        $category = Category::firstOrCreate(['name_ar' => 'C', 'name_en' => 'C']);

        return Product::create(array_merge([
            'category_id' => $category->id, 'name_ar' => "P{$pounds}", 'name_en' => "P{$pounds}",
            'price' => $pounds * 100, 'stock' => 50, 'status' => true,
        ], $extra));
    }

    private function quantities(User $user): array
    {
        return $user->cart->items()->pluck('quantity', 'product_id')->map(fn ($q) => (int) $q)->all();
    }

    public function test_guest_items_join_the_account_cart()
    {
        $user = User::factory()->create();
        $inBoth = $this->product(100);
        $onlyAccount = $this->product(50);
        $onlyGuest = $this->product(80, ['discount_price' => 6000]);

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $inBoth->id, 'quantity' => 1, 'price_snapshot' => 10000]);
        $cart->items()->create(['product_id' => $onlyAccount->id, 'quantity' => 2, 'price_snapshot' => 5000]);

        $response = $this->actingAs($user)->postJson('/api/cart/merge', ['items' => [
            ['product_id' => $inBoth->id, 'quantity' => 3],
            ['product_id' => $onlyGuest->id, 'quantity' => 2],
        ]])->assertOk();

        $this->assertSame([$inBoth->id => 3, $onlyAccount->id => 2, $onlyGuest->id => 2], $this->quantities($user->fresh()));
        $this->assertCount(3, $response->json('data.items'));
        $this->assertSame(6000, $user->cart->items()->where('product_id', $onlyGuest->id)->value('price_snapshot'), 'priced like adding to cart');
    }

    public function test_merging_twice_does_not_double_quantities()
    {
        $user = User::factory()->create();
        $p = $this->product(100);
        $items = ['items' => [['product_id' => $p->id, 'quantity' => 2]]];

        $this->actingAs($user)->postJson('/api/cart/merge', $items)->assertOk();
        $this->actingAs($user)->postJson('/api/cart/merge', $items)->assertOk();

        $this->assertSame([$p->id => 2], $this->quantities($user->fresh()));
    }

    public function test_account_quantity_is_kept_when_larger()
    {
        $user = User::factory()->create();
        $p = $this->product(100);
        Cart::create(['user_id' => $user->id])->items()->create(['product_id' => $p->id, 'quantity' => 5, 'price_snapshot' => 10000]);

        $this->actingAs($user)->postJson('/api/cart/merge', ['items' => [['product_id' => $p->id, 'quantity' => 2]]])->assertOk();

        $this->assertSame([$p->id => 5], $this->quantities($user->fresh()));
    }

    public function test_deleted_hidden_and_unknown_products_are_skipped()
    {
        $user = User::factory()->create();
        $ok = $this->product(100);
        $hidden = $this->product(60, ['status' => false]);
        $deleted = $this->product(70);
        $deleted->delete();

        $this->actingAs($user)->postJson('/api/cart/merge', ['items' => [
            ['product_id' => $ok->id, 'quantity' => 1],
            ['product_id' => $hidden->id, 'quantity' => 1],
            ['product_id' => $deleted->id, 'quantity' => 1],
            ['product_id' => 99999, 'quantity' => 1],
        ]])->assertOk();

        $this->assertSame([$ok->id => 1], $this->quantities($user->fresh()));
    }

    public function test_empty_guest_cart_and_guests()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/cart/merge', ['items' => []])->assertOk();
        $this->assertSame([], $this->quantities($user->fresh()));

        auth()->forgetGuards();
        $this->postJson('/api/cart/merge', ['items' => []])->assertUnauthorized();
    }
}
