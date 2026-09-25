<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class MoneyMinorUnitsTest extends TestCase
{
    use RefreshDatabase;

    private function migration()
    {
        return require database_path('migrations/2026_09_25_000000_convert_money_columns_to_minor_units.php');
    }

    private function seedLegacyRows(array $couponOverrides = []): void
    {
        $categoryId = Category::create(['name_ar' => 'Cat', 'name_en' => 'Cat'])->id;

        DB::table('products')->insert([
            ['id' => 1, 'category_id' => $categoryId, 'name_ar' => 'A', 'name_en' => 'A', 'price' => 19.99, 'discount_price' => null, 'cost_price' => 10.5, 'shipping_cost' => 0, 'stock' => 1],
            ['id' => 2, 'category_id' => $categoryId, 'name_ar' => 'B', 'name_en' => 'B', 'price' => 1250.75, 'discount_price' => 999.01, 'cost_price' => null, 'shipping_cost' => 25.5, 'stock' => 1],
        ]);

        DB::table('coupons')->insert([
            ['code' => 'FIXED', 'type' => 'fixed', 'value' => 50.25, 'min_order_amount' => 300.1, 'times_used' => 0],
            array_merge(['code' => 'PCT', 'type' => 'percent', 'value' => 10, 'min_order_amount' => null, 'times_used' => 0], $couponOverrides),
        ]);
    }

    public function test_migration_converts_pounds_to_piasters_and_rolls_back_exactly()
    {
        $migration = $this->migration();
        $migration->down();
        $this->seedLegacyRows();

        $migration->up();

        $this->assertEquals(
            [1 => [1999, null, 1050, 0], 2 => [125075, 99901, null, 2550]],
            DB::table('products')->orderBy('id')->get()
                ->mapWithKeys(fn ($p) => [$p->id => [(int) $p->price, $p->discount_price === null ? null : (int) $p->discount_price, $p->cost_price === null ? null : (int) $p->cost_price, (int) $p->shipping_cost]])
                ->all()
        );
        $this->assertEquals(5025, DB::table('coupons')->where('code', 'FIXED')->value('value'));
        $this->assertEquals(30010, DB::table('coupons')->where('code', 'FIXED')->value('min_order_amount'));
        $this->assertEquals(10, DB::table('coupons')->where('code', 'PCT')->value('value'), 'percent coupons keep their percentage');
        $this->assertEquals(19.99, (float) DB::table('products')->where('id', 1)->value('price_legacy'), 'legacy backup is kept');

        // A row written after the conversion must survive a rollback too.
        DB::table('products')->where('id', 1)->update(['price' => 2050]);

        $migration->down();

        $this->assertEquals(20.50, (float) DB::table('products')->where('id', 1)->value('price'));
        $this->assertEquals(999.01, (float) DB::table('products')->where('id', 2)->value('discount_price'));
        $this->assertNull(DB::table('products')->where('id', 2)->value('cost_price'));
        $this->assertEquals(50.25, (float) DB::table('coupons')->where('code', 'FIXED')->value('value'));
        $this->assertEquals(10, (float) DB::table('coupons')->where('code', 'PCT')->value('value'));

        $migration->up();
    }

    public function test_migration_refuses_fractional_percent_coupons()
    {
        $migration = $this->migration();
        $migration->down();
        $this->seedLegacyRows(['value' => 12.5]);

        try {
            $migration->up();
            $this->fail('Migration should have refused a fractional percent coupon.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('PCT', $e->getMessage());
        }

        // Nothing was touched.
        $this->assertEquals(19.99, (float) DB::table('products')->where('id', 1)->value('price'));

        DB::table('coupons')->where('code', 'PCT')->update(['value' => 12]);
        $migration->up();
    }

    public function test_money_cast_rejects_pounds_with_fractions()
    {
        $this->expectException(InvalidArgumentException::class);

        new Product(['price' => 19.99]);
    }

    public function test_money_helper_round_trips()
    {
        $this->assertSame(1999, Money::fromPounds('19.99'));
        $this->assertSame(1, Money::fromPounds('0.01'));
        $this->assertNull(Money::fromPounds(null));
        $this->assertSame('19.99', Money::toDecimalString(1999));
        $this->assertSame('1,250.00', Money::format(125000));
    }

    public function test_raw_model_responses_keep_pound_strings()
    {
        $user = User::factory()->create();
        $category = Category::create(['name_ar' => 'Cat', 'name_en' => 'Cat']);
        $product = Product::create([
            'category_id' => $category->id, 'name_ar' => 'P', 'name_en' => 'P',
            'price' => 25000, 'discount_price' => 19999, 'stock' => 5,
        ]);
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price_snapshot' => 19999]);

        $response = $this->actingAs($user)->getJson('/api/cart')->assertOk();

        $item = collect($response->json('data.items'))->first();
        $this->assertSame('199.99', $item['price_snapshot']);
        $this->assertSame('250.00', $item['product']['price']);
        $this->assertSame('199.99', $item['product']['final_price']);
        $this->assertArrayNotHasKey('price_legacy', $item['product']);
    }

    public function test_fixed_coupon_input_is_stored_in_piasters_and_percent_as_is()
    {
        $this->assertSame(5050, Coupon::fromInput(['type' => 'fixed', 'value' => '50.50', 'min_order_amount' => '300'])['value']);
        $this->assertSame(30000, Coupon::fromInput(['type' => 'fixed', 'value' => '50.50', 'min_order_amount' => '300'])['min_order_amount']);
        $this->assertSame('15', Coupon::fromInput(['type' => 'percent', 'value' => '15'])['value']);

        $coupon = Coupon::create(Coupon::fromInput(['code' => 'X', 'type' => 'percent', 'value' => 15]));
        $this->assertSame(1500, $coupon->calculateDiscount(10000));
        $this->assertSame(1499, $coupon->calculateDiscount(9993)); // 1498.95 rounds to 1499
    }
}
