<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Search and filters on the admin lists (orders and the catalog pages).
 */
class AdminFiltersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Ids shown on an admin list for the given query string, sorted.
     */
    private function listed(string $route, array $query, string $key): array
    {
        $ids = [];
        $this->actingAs($this->admin)->get(route($route, $query))
            ->assertOk()
            ->assertViewHas($key, function ($paginator) use (&$ids) {
                $ids = $paginator->pluck('id')->sort()->values()->all();
                return true;
            });

        return $ids;
    }

    private function order(array $attributes = []): Order
    {
        $createdAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $order = Order::create(array_merge([
            'user_id' => null,
            'full_name' => 'عميل',
            'phone_number' => '01000000000',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'address_line' => 'Street 1',
            'status' => 'pending',
            'payment_method' => 'cash',
            'subtotal' => 10000,
            'total_amount' => 10000,
        ], $attributes));

        if ($createdAt) {
            $order->forceFill(['created_at' => $createdAt])->save();
        }

        return $order;
    }

    public function test_orders_search_by_number_name_phone_and_account(): void
    {
        $customer = User::factory()->create(['name' => 'Mona Account', 'email' => 'mona@example.com']);
        $byName = $this->order(['full_name' => 'أحمد سمير']);
        $byPhone = $this->order(['phone_number' => '01122334455']);
        $byBackup = $this->order(['phone_number_backup' => '01299887766']);
        $byAccount = $this->order(['user_id' => $customer->id]);

        $this->assertSame([$byName->id], $this->listed('admin.orders.index', ['q' => 'سمير'], 'orders'));
        $this->assertSame([$byPhone->id], $this->listed('admin.orders.index', ['q' => '22334'], 'orders'));
        $this->assertSame([$byBackup->id], $this->listed('admin.orders.index', ['q' => '99887'], 'orders'));
        $this->assertSame([$byAccount->id], $this->listed('admin.orders.index', ['q' => 'mona@example'], 'orders'));
        $this->assertSame([$byAccount->id], $this->listed('admin.orders.index', ['q' => 'Mona Acc'], 'orders'));
        $this->assertSame([$byPhone->id], $this->listed('admin.orders.index', ['q' => '#' . $byPhone->id], 'orders'));
    }

    public function test_orders_filter_by_status_and_discount(): void
    {
        $offer = Offer::create(['name_ar' => 'باقة', 'name_en' => 'Bundle', 'type' => 'bundle', 'bundle_price' => 5000, 'is_active' => true]);

        $plain = $this->order();
        $shipped = $this->order(['status' => 'shipped']);
        $withCoupon = $this->order(['coupon_code' => 'SAVE10']);
        $withOffer = $this->order(['offer_id' => $offer->id]);
        // The offer was deleted later: only the snapshot is left
        $withOldOffer = $this->order(['offer_snapshot' => ['name_ar' => 'قديم']]);

        $this->assertSame([$shipped->id], $this->listed('admin.orders.index', ['status' => 'shipped'], 'orders'));
        $this->assertSame([$withCoupon->id], $this->listed('admin.orders.index', ['discount' => 'coupon'], 'orders'));
        $this->assertSame([$withOffer->id, $withOldOffer->id], $this->listed('admin.orders.index', ['discount' => 'offer'], 'orders'));
        $this->assertSame([$plain->id, $shipped->id], $this->listed('admin.orders.index', ['discount' => 'none'], 'orders'));
        $this->assertSame([], $this->listed('admin.orders.index', ['status' => 'shipped', 'discount' => 'coupon'], 'orders'));
    }

    public function test_orders_date_range_uses_egypt_days(): void
    {
        // 23:30 UTC on Sep 30 is 02:30 on Oct 1 in Cairo (UTC+3 in summer time)
        $lateNight = $this->order(['created_at' => '2026-09-30 23:30:00']);
        $sep30 = $this->order(['created_at' => '2026-09-30 10:00:00']);
        $oct2 = $this->order(['created_at' => '2026-10-02 12:00:00']);

        $this->assertSame([$lateNight->id], $this->listed('admin.orders.index', ['from' => '2026-10-01', 'to' => '2026-10-01'], 'orders'));
        $this->assertSame([$sep30->id], $this->listed('admin.orders.index', ['to' => '2026-09-30'], 'orders'));
        $this->assertSame([$lateNight->id, $oct2->id], $this->listed('admin.orders.index', ['from' => '2026-10-01'], 'orders'));
    }

    public function test_invalid_filter_values_show_the_whole_list(): void
    {
        $orders = [$this->order()->id, $this->order(['status' => 'delivered'])->id];

        $this->assertSame($orders, $this->listed('admin.orders.index', ['status' => 'nope', 'discount' => 'gold', 'from' => '2026-13-45', 'to' => 'abc', 'q' => '  '], 'orders'));
        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => ['pending']]))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.products.index', ['category' => '999', 'stock' => 'lots']))->assertOk();
    }

    public function test_pagination_links_keep_the_filters(): void
    {
        foreach (range(1, 16) as $i) {
            $this->order(['status' => 'processing']);
        }

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'processing']))
            ->assertOk()
            ->assertSee('status=processing&amp;page=2', false);
    }

    public function test_changing_an_order_status_returns_to_the_filtered_list(): void
    {
        $order = $this->order();
        $list = route('admin.orders.index', ['status' => 'pending', 'page' => 1]);

        $this->actingAs($this->admin)->from($list)
            ->put(route('admin.orders.update', $order), ['status' => 'processing'])
            ->assertRedirect($list);
    }

    public function test_filtered_empty_list_offers_to_clear_the_filters(): void
    {
        $this->order();

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => 'لا يوجد']))
            ->assertOk()
            ->assertSee('لا توجد نتائج مطابقة')
            ->assertSee('مسح الفلاتر');
    }

    public function test_products_search_and_filters(): void
    {
        $lamps = Category::factory()->create();
        $cables = Category::factory()->create();
        $lamp = Product::factory()->create(['category_id' => $lamps->id, 'name_ar' => 'لمبة ليد', 'name_en' => 'LED bulb', 'stock' => 50, 'status' => true]);
        $cable = Product::factory()->create(['category_id' => $cables->id, 'name_ar' => 'سلك', 'name_en' => 'Cable', 'stock' => 3, 'status' => true]);
        $soldOut = Product::factory()->create(['category_id' => $cables->id, 'name_ar' => 'فيشة', 'name_en' => 'Plug', 'stock' => 0, 'status' => false]);

        $this->assertSame([$lamp->id], $this->listed('admin.products.index', ['q' => 'ليد'], 'products'));
        $this->assertSame([$lamp->id], $this->listed('admin.products.index', ['q' => 'bulb'], 'products'));
        $this->assertSame([$cable->id, $soldOut->id], $this->listed('admin.products.index', ['category' => $cables->id], 'products'));
        $this->assertSame([$soldOut->id], $this->listed('admin.products.index', ['status' => 'inactive'], 'products'));
        $this->assertSame([$cable->id], $this->listed('admin.products.index', ['stock' => 'low'], 'products'));
        $this->assertSame([$soldOut->id], $this->listed('admin.products.index', ['stock' => 'out'], 'products'));
        $this->assertSame([$lamp->id, $cable->id], $this->listed('admin.products.index', ['stock' => 'in'], 'products'));
    }

    public function test_categories_search_and_status(): void
    {
        $on = Category::factory()->create(['name_ar' => 'إضاءة', 'name_en' => 'Lighting', 'status' => true]);
        $off = Category::factory()->create(['name_ar' => 'أسلاك', 'name_en' => 'Wires', 'status' => false]);

        $this->assertSame([$on->id], $this->listed('admin.categories.index', ['q' => 'Light'], 'categories'));
        $this->assertSame([$off->id], $this->listed('admin.categories.index', ['q' => 'أسلاك'], 'categories'));
        $this->assertSame([$off->id], $this->listed('admin.categories.index', ['status' => 'inactive'], 'categories'));
    }

    public function test_offers_filter_by_type_and_state(): void
    {
        $base = ['name_en' => 'Offer', 'type' => 'bundle', 'bundle_price' => 5000, 'is_active' => true];
        $active = Offer::create(['name_ar' => 'باقة الصيف'] + $base);
        $scheduled = Offer::create(['name_ar' => 'قادم', 'starts_at' => now()->addDay()] + $base);
        $expired = Offer::create(['name_ar' => 'قديم', 'expires_at' => now()->subDay()] + $base);
        $off = Offer::create(['name_ar' => 'موقوف', 'is_active' => false] + $base);
        $buyGet = Offer::create(['name_ar' => 'اشتري', 'name_en' => 'Buy', 'type' => 'buy_x_get_y', 'buy_quantity' => 2, 'get_quantity' => 1, 'get_discount_percent' => 100, 'is_active' => true]);

        $this->assertSame([$active->id], $this->listed('admin.offers.index', ['q' => 'الصيف'], 'offers'));
        $this->assertSame([$buyGet->id], $this->listed('admin.offers.index', ['type' => 'buy_x_get_y'], 'offers'));
        $this->assertSame([$active->id, $buyGet->id], $this->listed('admin.offers.index', ['state' => 'active'], 'offers'));
        $this->assertSame([$scheduled->id], $this->listed('admin.offers.index', ['state' => 'scheduled'], 'offers'));
        $this->assertSame([$expired->id], $this->listed('admin.offers.index', ['state' => 'expired'], 'offers'));
        $this->assertSame([$off->id], $this->listed('admin.offers.index', ['state' => 'inactive'], 'offers'));

        // The list badge says the same as the filter
        foreach (['active' => $active, 'scheduled' => $scheduled, 'expired' => $expired, 'inactive' => $off] as $state => $offer) {
            $this->assertSame($state, $offer->state());
        }
    }

    public function test_coupons_search_and_state(): void
    {
        $valid = Coupon::create(['code' => 'SUMMER10', 'type' => 'percent', 'value' => 10]);
        $limited = Coupon::create(['code' => 'LIMITED', 'type' => 'fixed', 'value' => 1000, 'max_uses' => 5, 'times_used' => 2]);
        $scheduled = Coupon::create(['code' => 'SOON', 'type' => 'percent', 'value' => 5, 'starts_at' => now()->addDay()]);
        $expired = Coupon::create(['code' => 'OLD', 'type' => 'percent', 'value' => 5, 'expires_at' => now()->subDay()]);
        $usedUp = Coupon::create(['code' => 'USEDUP', 'type' => 'fixed', 'value' => 1000, 'max_uses' => 3, 'times_used' => 3]);

        $this->assertSame([$valid->id], $this->listed('admin.coupons.index', ['q' => 'summer'], 'coupons'));
        $this->assertSame([$limited->id, $usedUp->id], $this->listed('admin.coupons.index', ['type' => 'fixed'], 'coupons'));
        $this->assertSame([$valid->id, $limited->id], $this->listed('admin.coupons.index', ['state' => 'valid'], 'coupons'));
        $this->assertSame([$scheduled->id], $this->listed('admin.coupons.index', ['state' => 'scheduled'], 'coupons'));
        $this->assertSame([$expired->id], $this->listed('admin.coupons.index', ['state' => 'expired'], 'coupons'));
        $this->assertSame([$usedUp->id], $this->listed('admin.coupons.index', ['state' => 'exhausted'], 'coupons'));

        foreach (['valid' => $limited, 'scheduled' => $scheduled, 'expired' => $expired, 'exhausted' => $usedUp] as $state => $coupon) {
            $this->assertSame($state, $coupon->state());
        }
    }
}
