<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Offers are bought directly: GET /api/offers/{id}/quote → POST /api/checkout/offer. The cart is never involved.
 * Prices are whole pounds so expected numbers read easily; products have no shipping cost, so shipping is the default 30.
 */
class OfferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(); // checkout reports to Meta
    }

    // ── helpers ────────────────────────────────────────────────────────

    private function product(int $pounds, int $stock = 100, array $extra = []): Product
    {
        $category = Category::firstOrCreate(['name_ar' => 'C', 'name_en' => 'C']);

        return Product::create(array_merge([
            'category_id' => $category->id, 'name_ar' => "P{$pounds}", 'name_en' => "P{$pounds}",
            'price' => $pounds * 100, 'stock' => $stock, 'status' => true,
        ], $extra));
    }

    /** bundle: $products = [product_id => quantity per set]; amounts in piasters. */
    private function bundle(int $pricePounds, array $products, array $extra = []): Offer
    {
        $offer = Offer::create(array_merge(['name_ar' => 'باقة', 'name_en' => 'Bundle', 'type' => 'bundle', 'bundle_price' => $pricePounds * 100, 'is_active' => true], $extra));
        $offer->products()->sync(collect($products)->map(fn ($q) => ['quantity' => $q])->all());

        return $offer;
    }

    private function buyXGetY(int $buy, int $get, array $productIds, array $extra = []): Offer
    {
        $offer = Offer::create(array_merge([
            'name_ar' => 'اشتري', 'name_en' => 'Buy', 'type' => 'buy_x_get_y',
            'buy_quantity' => $buy, 'get_quantity' => $get, 'get_discount_percent' => 100, 'is_active' => true,
        ], $extra));
        $offer->products()->sync($productIds);

        return $offer;
    }

    private function quote(Offer $offer, array $query = [])
    {
        return $this->getJson("/api/offers/{$offer->id}/quote?" . http_build_query($query));
    }

    private function buy(Offer $offer, array $data = [], array $headers = [])
    {
        $quote = $this->quote($offer, array_intersect_key($data, array_flip(['sets', 'product_id'])))->json('data');

        return $this->withHeaders($headers)->postJson('/api/checkout/offer', array_merge([
            'full_name' => 'G', 'phone_number' => '1', 'city' => 'C', 'state' => 'S',
            'shipping_way' => 'home', 'payment_method' => 'cash',
            'offer_id' => $offer->id, 'sets' => 1, 'expected_total' => $quote['total'] ?? 0,
        ], $data));
    }

    private function assertQuote($response, float $subtotal, float $discount, float $total): void
    {
        $response->assertOk()->assertJsonPath('data.purchasable', true);
        // assertEquals, not assertJsonPath: JSON turns 180.0 into 180, which a strict comparison rejects.
        $this->assertEquals([$subtotal, $discount, $total], [
            $response->json('data.subtotal'), $response->json('data.discount'), $response->json('data.total'),
        ]);
    }

    // ── quote: every offer shape ───────────────────────────────────────

    public function test_bundle_of_one_product_replaces_the_old_quantity_offers()
    {
        $p = $this->product(100);
        $offer = $this->bundle(250, [$p->id => 3]); // "3 × P for 250"

        $this->assertQuote($this->quote($offer), 300, 50, 280);
        $this->assertQuote($this->quote($offer, ['sets' => 2]), 600, 100, 530);
        $this->assertSame(6, $this->quote($offer, ['sets' => 2])->json('data.items.0.quantity'));
    }

    public function test_bundle_of_several_products()
    {
        $a = $this->product(100);
        $b = $this->product(80);
        $offer = $this->bundle(150, [$a->id => 1, $b->id => 1]);

        $this->assertQuote($this->quote($offer), 180, 30, 180);
        $this->assertQuote($this->quote($offer, ['sets' => 2]), 360, 60, 330);
    }

    public function test_buy_two_get_one_free_and_half_price()
    {
        $p = $this->product(100);

        $free = $this->buyXGetY(2, 1, [$p->id]);
        $this->assertQuote($this->quote($free), 300, 100, 230);
        $this->assertQuote($this->quote($free, ['sets' => 2]), 600, 200, 430);

        $half = $this->buyXGetY(2, 1, [$p->id], ['get_discount_percent' => 50]);
        $this->assertQuote($this->quote($half), 300, 50, 280);
    }

    public function test_buy_x_with_a_different_gift_product()
    {
        $p = $this->product(100);
        $gift = $this->product(40);
        $offer = $this->buyXGetY(2, 1, [$p->id], ['get_product_id' => $gift->id]);

        $response = $this->quote($offer, ['sets' => 2]);

        $this->assertQuote($response, 400, 0, 430);
        $this->assertSame(4, $response->json('data.items.0.quantity'));
        $this->assertSame(['product_id' => $gift->id, 'quantity' => 2], array_intersect_key($response->json('data.gifts.0'), array_flip(['product_id', 'quantity'])));
    }

    public function test_buy_x_with_several_products_needs_a_product_choice()
    {
        $a = $this->product(100);
        $b = $this->product(60);
        $other = $this->product(50);
        $offer = $this->buyXGetY(2, 1, [$a->id, $b->id]);

        $this->quote($offer)->assertStatus(422)->assertJsonValidationErrors('product_id');
        $this->quote($offer, ['product_id' => $other->id])->assertStatus(422)->assertJsonValidationErrors('product_id');
        $this->assertQuote($this->quote($offer, ['product_id' => $b->id]), 180, 60, 150);
    }

    public function test_sets_must_be_in_range()
    {
        $offer = $this->bundle(150, [$this->product(100)->id => 2]);

        $this->quote($offer, ['sets' => 0])->assertStatus(422);
        $this->quote($offer, ['sets' => 21])->assertStatus(422);
    }

    // ── quote: offer / stock / product problems ────────────────────────

    public function test_inactive_expired_and_future_offers_are_unavailable()
    {
        $p = $this->product(100);

        foreach ([
            ['is_active' => false],
            ['expires_at' => now()->subMinute()],
            ['starts_at' => now()->addDay()],
        ] as $state) {
            $offer = $this->bundle(150, [$p->id => 2], $state);
            $this->quote($offer)->assertStatus(404)->assertJsonPath('errors.code', 'offer_unavailable');
            $this->buy($offer)->assertStatus(422)->assertJsonPath('errors.code', 'offer_unavailable');
        }
    }

    public function test_out_of_stock_is_reported_before_paying()
    {
        $p = $this->product(100, stock: 5);
        $offer = $this->bundle(250, [$p->id => 3]);

        $this->quote($offer)->assertJsonPath('data.purchasable', true);
        $this->quote($offer, ['sets' => 2])
            ->assertJsonPath('data.purchasable', false)
            ->assertJsonPath('data.issues.0.code', 'out_of_stock');

        $this->buy($offer, ['sets' => 2])->assertStatus(422)->assertJsonPath('errors.code', 'out_of_stock');
        $this->assertSame(0, Order::count());
    }

    public function test_gift_stock_counts_together_with_paid_units_of_the_same_product()
    {
        $p = $this->product(100, stock: 3);
        // buy 2 of P, get 1 more P as a "gift product": needs 3 units in total
        $offer = $this->buyXGetY(2, 1, [$p->id], ['get_product_id' => $p->id]);

        $this->quote($offer)->assertJsonPath('data.purchasable', true);
        $this->quote($offer, ['sets' => 2])->assertJsonPath('data.issues.0.code', 'out_of_stock');
    }

    public function test_unavailable_products_block_the_offer()
    {
        $a = $this->product(100);
        $b = $this->product(80);
        $offer = $this->bundle(150, [$a->id => 1, $b->id => 1]);

        $b->update(['status' => false]);
        $this->quote($offer)->assertJsonPath('data.issues.0.code', 'product_unavailable');

        $b->update(['status' => true]);
        $b->delete(); // soft delete: the bundle must not silently shrink to just A
        $this->quote($offer)->assertJsonPath('data.purchasable', false);
        $this->assertCount(2, $this->quote($offer)->json('data.items'));
    }

    public function test_bundle_that_no_longer_saves_money_is_blocked()
    {
        $p = $this->product(100);
        $offer = $this->bundle(250, [$p->id => 3]);

        $p->update(['discount_price' => 8000]); // 3 × 80 = 240 < 250

        $this->quote($offer)->assertJsonPath('data.issues.0.code', 'no_saving');
    }

    // ── offer checkout ─────────────────────────────────────────────────

    public function test_buying_an_offer_creates_the_order_and_leaves_the_cart_alone()
    {
        $user = User::factory()->create();
        $p = $this->product(100);
        $other = $this->product(50);
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $other->id, 'quantity' => 2, 'price_snapshot' => 5000]);
        $offer = $this->bundle(250, [$p->id => 3]);

        $this->actingAs($user);
        $response = $this->buy($offer, ['sets' => 2]);

        $response->assertCreated()->assertJsonPath('data.offer.name_ar', 'باقة')->assertJsonPath('data.offer.sets', 2);
        $this->assertEquals([600, 100, 30, 530], [
            $response->json('data.subtotal'), $response->json('data.offer_discount'),
            $response->json('data.shipping_cost'), $response->json('data.total_amount'),
        ]);
        $this->assertSame(94, $p->fresh()->stock);
        $this->assertSame(1, $cart->items()->count(), 'cart is untouched');

        $order = Order::first();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame($offer->id, $order->offer_id);
        $this->assertSame(25000, $order->offer_snapshot['bundle_price']);
        $this->assertSame([['id' => $p->id, 'name_ar' => 'P100', 'quantity' => 3]], $order->offer_snapshot['products']);
    }

    public function test_gift_becomes_a_free_order_line()
    {
        $p = $this->product(100);
        $gift = $this->product(40, stock: 5);
        $offer = $this->buyXGetY(2, 1, [$p->id], ['get_product_id' => $gift->id]);

        $response = $this->buy($offer, ['sets' => 2])->assertCreated();

        $giftLine = collect($response->json('data.items'))->firstWhere('product_id', $gift->id);
        $this->assertSame(2, $giftLine['quantity']);
        $this->assertEquals(0, $giftLine['total']);
        $this->assertSame(3, $gift->fresh()->stock);
        $this->assertEquals(430, $response->json('data.total_amount')); // gift ships free
    }

    public function test_price_change_after_the_quote_is_not_charged_silently()
    {
        $p = $this->product(100);
        $offer = $this->bundle(250, [$p->id => 3]);
        $shownTotal = $this->quote($offer)->json('data.total'); // 280

        $offer->update(['bundle_price' => 27000]); // admin edits meanwhile

        $this->buy($offer, ['expected_total' => $shownTotal])
            ->assertStatus(409)
            ->assertJsonPath('errors.code', 'price_changed')
            ->assertJsonPath('errors.quote.total', 300);
        $this->assertSame(0, Order::count());

        $this->buy($offer, ['expected_total' => 300])->assertCreated();
    }

    public function test_expected_total_is_required()
    {
        $offer = $this->bundle(250, [$this->product(100)->id => 3]);

        $this->buy($offer, ['expected_total' => null])->assertStatus(422)->assertJsonValidationErrors('expected_total');
    }

    public function test_double_submit_with_the_same_key_creates_one_order()
    {
        $p = $this->product(100);
        $offer = $this->bundle(250, [$p->id => 3]);
        $headers = ['Idempotency-Key' => 'checkout-attempt-1'];

        $first = $this->buy($offer, [], $headers)->assertCreated();
        $second = $this->buy($offer, [], $headers)->assertOk();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(1, Order::count());
        $this->assertSame(97, $p->fresh()->stock);
    }

    public function test_order_keeps_the_offer_as_bought_after_the_offer_changes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $p = $this->product(100);
        $offer = $this->bundle(250, [$p->id => 3], ['name_ar' => 'الاسم القديم']);
        $orderId = $this->buy($offer)->json('data.id');

        $offer->update(['name_ar' => 'الاسم الجديد', 'bundle_price' => 20000]);
        $offer->delete();

        $this->actingAs($admin)->get("/admin/orders/{$orderId}")
            ->assertOk()
            ->assertSee('الاسم القديم')
            ->assertSee('باقة بسعر 250.00')
            ->assertDontSee('الاسم الجديد');
    }

    public function test_checkout_still_succeeds_when_meta_tracking_is_down()
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Meta is down'));
        $p = $this->product(100);
        $offer = $this->bundle(250, [$p->id => 3]);

        $this->buy($offer)->assertCreated();
        $this->postJson('/api/checkout', [
            'full_name' => 'G', 'phone_number' => '1', 'city' => 'C', 'state' => 'S', 'shipping_way' => 'home',
            'payment_method' => 'cash', 'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertCreated();

        $this->assertSame(2, Order::count());
    }

    // ── cart checkout no longer knows about offers ─────────────────────

    public function test_cart_checkout_rejects_offer_id()
    {
        $p = $this->product(100);

        $this->postJson('/api/checkout', [
            'full_name' => 'G', 'phone_number' => '1', 'city' => 'C', 'state' => 'S', 'shipping_way' => 'home',
            'payment_method' => 'cash', 'offer_id' => 1, 'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('offer_id');
    }

    public function test_cart_quantity_no_longer_changes_the_price()
    {
        $p = $this->product(100);
        $this->bundle(250, [$p->id => 3]); // exists, but only applies when bought from the offer page

        $response = $this->postJson('/api/checkout', [
            'full_name' => 'G', 'phone_number' => '1', 'city' => 'C', 'state' => 'S', 'shipping_way' => 'home',
            'payment_method' => 'cash', 'items' => [['product_id' => $p->id, 'quantity' => 3]],
        ])->assertCreated();

        $this->assertEquals(330, $response->json('data.total_amount'));
    }

    public function test_cart_checkout_guards_total_and_stock_across_duplicate_lines()
    {
        $p = $this->product(100, stock: 5);
        $base = ['full_name' => 'G', 'phone_number' => '1', 'city' => 'C', 'state' => 'S', 'shipping_way' => 'home', 'payment_method' => 'cash'];

        $this->postJson('/api/checkout', $base + ['items' => [['product_id' => $p->id, 'quantity' => 1]], 'expected_total' => 99])
            ->assertStatus(409);

        // 3 + 3 of the same product with 5 in stock: each line alone fits, together they do not
        $this->postJson('/api/checkout', $base + ['items' => [['product_id' => $p->id, 'quantity' => 3], ['product_id' => $p->id, 'quantity' => 3]]])
            ->assertStatus(422);
        $this->assertSame(5, $p->fresh()->stock);
    }

    // ── admin ──────────────────────────────────────────────────────────

    public function test_admin_creates_offers_in_pounds_with_cairo_times()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $a = $this->product(100);
        $b = $this->product(80);
        $base = ['name_ar' => 'ع', 'name_en' => 'O', 'is_active' => 1];

        $this->actingAs($admin)->post('/admin/offers', $base + [
            'type' => 'bundle', 'bundle_price' => '249.50', 'products' => [$a->id], 'quantities' => [$a->id => 3],
            'starts_at' => '2026-10-01T10:00',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/offers', $base + [
            'type' => 'buy_x_get_y', 'buy_quantity' => 2, 'get_quantity' => 1, 'get_discount_percent' => 50, 'products' => [$a->id, $b->id],
        ])->assertRedirect()->assertSessionHasNoErrors();

        [$bundle, $buy] = Offer::orderBy('id')->get();
        $this->assertSame(24950, $bundle->bundle_price);
        $this->assertSame(3, $bundle->products->first()->pivot->quantity);
        $this->assertSame('2026-10-01 07:00', $bundle->starts_at->format('Y-m-d H:i')); // stored in UTC
        $this->assertNull($buy->bundle_price);
        $this->assertSame(50, $buy->get_discount_percent);
        $this->assertCount(2, $buy->products);

        // The edit form shows pounds, the quantity, and the same Cairo time, so saving again changes nothing.
        $this->actingAs($admin)->get("/admin/offers/{$bundle->id}/edit")->assertOk()
            ->assertSee('value="249.5"', false)
            ->assertSee('name="quantities[' . $a->id . ']" value="3"', false)
            ->assertSee('value="2026-10-01T10:00"', false);
    }

    public function test_admin_validation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $a = $this->product(100);
        $gift = $this->product(40);
        $base = ['name_ar' => 'ع', 'name_en' => 'O'];
        $post = fn (array $data) => $this->actingAs($admin)->post('/admin/offers', $base + $data);

        $post(['type' => 'percentage', 'products' => [$a->id]])->assertSessionHasErrors('type'); // coupons do this now
        $post(['type' => 'bundle', 'bundle_price' => '100'])->assertSessionHasErrors('products');
        $post(['type' => 'bundle', 'bundle_price' => '90', 'products' => [$a->id], 'quantities' => [$a->id => 1]])
            ->assertSessionHasErrors('products'); // a single unit is not a bundle
        $post(['type' => 'bundle', 'bundle_price' => '300', 'products' => [$a->id], 'quantities' => [$a->id => 3]])
            ->assertSessionHasErrors('bundle_price'); // not cheaper than 3 × 100
        $post(['type' => 'buy_x_get_y', 'products' => [$a->id]])->assertSessionHasErrors(['buy_quantity', 'get_quantity']);
        $post(['type' => 'buy_x_get_y', 'buy_quantity' => 2, 'get_quantity' => 1, 'get_product_id' => $gift->id, 'get_discount_percent' => 50, 'products' => [$a->id]])
            ->assertSessionHasErrors('get_discount_percent');

        $this->assertSame(0, Offer::count());
    }

    // ── API shape ──────────────────────────────────────────────────────

    public function test_offers_api_returns_pounds_and_bundle_quantities()
    {
        $p = $this->product(100);
        $this->bundle(250, [$p->id => 3]);

        $offer = $this->getJson('/api/offers/all')->assertOk()->json('data.0');

        $this->assertSame('250.00', $offer['bundle_price']);
        $this->assertSame('100.00', $offer['products'][0]['price']);
        $this->assertSame(3, $offer['products'][0]['pivot']['quantity']);
        $this->assertArrayNotHasKey('legacy_bundle_offer_id', $offer);
    }

    public function test_product_api_no_longer_has_bundle_offers()
    {
        $p = $this->product(100);

        $this->assertArrayNotHasKey('bundle_offers', $this->getJson("/api/products/{$p->id}")->assertOk()->json('data'));
    }

    // ── migrations ─────────────────────────────────────────────────────

    private function migration(string $name)
    {
        return require database_path("migrations/{$name}.php");
    }

    public function test_old_quantity_offers_move_to_bundle_offers_and_back()
    {
        $move = $this->migration('2026_09_27_000001_move_product_bundle_offers_to_offers');
        $move->down();

        $p = $this->product(100);
        $gone = $this->product(80);
        $gone->delete();
        DB::table('product_bundle_offers')->insert([
            ['id' => 7, 'product_id' => $p->id, 'quantity' => 3, 'bundle_price' => 25000, 'is_active' => true],
            ['id' => 8, 'product_id' => $gone->id, 'quantity' => 2, 'bundle_price' => 15000, 'is_active' => true],
        ]);
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $p->id, 'quantity' => 3, 'price_snapshot' => 8333]); // priced by the old offer

        $move->up();

        $offer = Offer::where('legacy_bundle_offer_id', 7)->firstOrFail();
        $this->assertSame(['bundle', 25000, true], [$offer->type, $offer->bundle_price, $offer->is_active]);
        $this->assertSame([$p->id => 3], $offer->products->mapWithKeys(fn ($x) => [$x->id => $x->pivot->quantity])->all());
        $this->assertSame('3 قطع من P100', $offer->name_ar);
        $this->assertFalse(Offer::where('legacy_bundle_offer_id', 8)->first()->is_active, 'deleted product → inactive offer');
        $this->assertSame(10000, $cart->items()->first()->price_snapshot, 'cart snapshot reset to the normal price');
        $this->assertSame(2, DB::table('product_bundle_offers')->count(), 'old table kept');

        $this->assertQuote($this->quote($offer), 300, 50, 280);

        $move->down();
        $this->assertSame(0, Offer::count());
        $move->up();
    }

    public function test_restructure_refuses_to_drop_offers_of_removed_types()
    {
        $move = $this->migration('2026_09_27_000001_move_product_bundle_offers_to_offers');
        $restructure = $this->migration('2026_09_27_000000_restructure_offers_for_direct_purchase');
        $move->down();
        $restructure->down();

        DB::table('offers')->insert(['id' => 5, 'name_ar' => 'O', 'name_en' => 'O', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        try {
            $restructure->up();
            $this->fail('An offer of a removed type must stop the migration.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ids 5', $e->getMessage());
        }
        $this->assertTrue(DB::table('offers')->where('id', 5)->exists(), 'nothing deleted');

        DB::table('offers')->delete();
        $restructure->up();
        $move->up();
    }

    public function test_offer_money_conversion_from_pounds_and_back()
    {
        $move = $this->migration('2026_09_27_000001_move_product_bundle_offers_to_offers');
        $restructure = $this->migration('2026_09_27_000000_restructure_offers_for_direct_purchase');
        $money = $this->migration('2026_09_26_000000_convert_offer_money_columns_to_minor_units');
        $move->down();
        $restructure->down();
        $money->down();

        $row = fn (array $a) => array_merge(['name_ar' => 'O', 'name_en' => 'O', 'is_active' => true, 'value' => null, 'bundle_price' => null], $a);
        DB::table('offers')->insert([
            $row(['id' => 1, 'type' => 'bundle', 'bundle_price' => 1999.95]),
            $row(['id' => 2, 'type' => 'buy_x_get_y']),
        ]);

        $money->up();
        $this->assertSame(199995, (int) DB::table('offers')->where('id', 1)->value('bundle_price'));

        $money->down();
        $this->assertEquals(1999.95, (float) DB::table('offers')->where('id', 1)->value('bundle_price'));

        $money->up();
        $restructure->up();
        $move->up();
        $this->assertSame(199995, Offer::find(1)->bundle_price);
    }
}
