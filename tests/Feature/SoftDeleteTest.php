<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductImage;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

/**
 * Nothing is deleted for real: a delete hides the row everywhere, and orders keep what they point at.
 */
class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @var string[] uploads created by file() */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(); // product views and checkout report to Meta
    }

    // ── helpers ────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function product(array $extra = []): Product
    {
        return Product::factory()->create(array_merge(['price' => 10000, 'stock' => 50], $extra));
    }

    private function order(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'full_name' => 'عميل', 'phone_number' => '01000000000', 'city' => 'Cairo', 'state' => 'Cairo',
            'status' => 'pending', 'payment_method' => 'cash', 'subtotal' => 10000, 'total_amount' => 10000,
        ], $attributes));
    }

    /** Put a file on the (faked) public disk and remember it. */
    private function file(string $path): string
    {
        Storage::disk('public')->put($path, 'x');
        $this->files[] = $path;

        return $path;
    }

    // ── admin deletes ──────────────────────────────────────────────────

    public function test_every_admin_delete_is_soft_and_keeps_the_files(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $product = $this->product(['image' => $this->file('products/p.jpg')]);

        $deletes = [
            'admin.banners.destroy' => Banner::create(['title_ar' => 'ب', 'title_en' => 'B', 'image' => $this->file('banners/b.jpg'), 'status' => true]),
            'admin.posts.destroy' => Post::create(['title_ar' => 'م', 'title_en' => 'P', 'description_ar' => 'و', 'description_en' => 'D', 'image' => $this->file('posts/p.jpg')]),
            'admin.offers.destroy' => Offer::create(['name_ar' => 'ع', 'name_en' => 'O', 'type' => 'bundle', 'bundle_price' => 5000, 'image' => $this->file('offers/o.jpg')]),
            'admin.partners.destroy' => Partner::create(['name_en' => 'Acme', 'type' => 'partner', 'logo' => $this->file('partners/l.png')]),
            'admin.certificates.destroy' => Certificate::create(['title_en' => 'ISO', 'image' => $this->file('certificates/c.jpg')]),
            'admin.team-members.destroy' => TeamMember::create(['name_en' => 'Sara', 'photo' => $this->file('team/s.jpg')]),
            'admin.images.destroy' => ProductImage::create(['product_id' => $product->id, 'image' => $this->file('products/extra.jpg')]),
            'admin.features.destroy' => ProductFeature::create(['product_id' => $product->id, 'name_ar' => 'م', 'name_en' => 'F']),
            'admin.coupons.destroy' => Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10]),
            'admin.products.destroy' => $product,
            'admin.categories.destroy' => $product->category,
            'admin.users.destroy' => User::factory()->create(),
        ];

        foreach ($deletes as $route => $model) {
            $this->actingAs($admin)->delete(route($route, $model))->assertRedirect();

            $this->assertSoftDeleted($model);
            $this->assertNull($model->newQuery()->find($model->getKey()), "{$route} still lists the row");
        }

        $this->assertCount(8, $this->files);
        Storage::disk('public')->assertExists($this->files);

        // The lists still render without the deleted rows
        foreach (['banners', 'posts', 'offers', 'partners', 'certificates', 'team-members', 'coupons', 'products', 'categories', 'users'] as $list) {
            $this->actingAs($admin)->get(route("admin.{$list}.index"))->assertOk();
        }
    }

    public function test_deleted_content_leaves_the_public_api(): void
    {
        $product = $this->product();
        $feature = ProductFeature::create(['product_id' => $product->id, 'name_ar' => 'م', 'name_en' => 'F']);
        $image = ProductImage::create(['product_id' => $product->id, 'image' => 'products/extra.jpg']);
        $banner = Banner::create(['title_ar' => 'ب', 'title_en' => 'B', 'image' => 'b.jpg', 'status' => true]);
        $post = Post::create(['title_ar' => 'م', 'title_en' => 'P', 'description_ar' => 'و', 'description_en' => 'D']);
        $partner = Partner::create(['name_en' => 'Acme', 'type' => 'partner', 'logo' => 'l.png']);
        $certificate = Certificate::create(['title_en' => 'ISO', 'image' => 'c.jpg']);
        $member = TeamMember::create(['name_en' => 'Sara']);

        foreach ([$feature, $image, $banner, $post, $partner, $certificate, $member] as $model) {
            $model->delete();
        }

        $this->getJson("/api/products/{$product->id}")->assertOk()
            ->assertJsonCount(0, 'data.features')
            ->assertJsonCount(0, 'data.extra_images');
        $this->getJson('/api/banners')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/posts')->assertOk()->assertJsonCount(0, 'data.posts');
        $this->getJson("/api/posts/{$post->id}")->assertNotFound();
        $this->getJson('/api/partners')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/certificates')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/team')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_customer_deletes_an_address_softly(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->deleteJson("/api/addresses/{$address->id}")->assertOk();

        $this->assertSoftDeleted($address);
        $this->actingAs($user)->getJson('/api/addresses')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($user)->getJson("/api/addresses/{$address->id}")->assertNotFound();
    }

    // ── orders survive ─────────────────────────────────────────────────

    public function test_orders_keep_their_product_customer_and_offer_after_they_are_deleted(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['name' => 'Mona Customer', 'email' => 'mona@example.com']);
        $product = $this->product(['name_ar' => 'لمبة ليد', 'name_en' => 'LED bulb']);
        $offer = Offer::create(['name_ar' => 'باقة الصيف', 'name_en' => 'Summer', 'type' => 'bundle', 'bundle_price' => 5000]);

        $order = $this->order(['user_id' => $customer->id, 'offer_id' => $offer->id]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'price' => 5000, 'total' => 10000]);

        $product->delete();
        $product->category->delete();
        $offer->delete();
        $customer->delete();

        $order = $order->fresh();
        $this->assertSame('mona@example.com', $order->user->email);
        $this->assertSame('باقة الصيف', $order->offer->name_ar);
        $this->assertSame('لمبة ليد', $order->items->first()->product->name_ar);

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('لمبة ليد')
            ->assertSee('Mona Customer')
            ->assertSee('باقة الصيف');
        $this->actingAs($admin)->get(route('admin.orders.index', ['q' => 'mona@example.com']))->assertOk()->assertSee('Mona Customer');

        $this->actingAs($admin)->getJson('/api/admin/orders')->assertOk()
            ->assertJsonPath('data.0.items.0.product.name_en', 'LED bulb')
            ->assertJsonPath('data.0.user.email', 'mona@example.com');
    }

    public function test_the_database_no_longer_cascades_real_deletes_into_orders(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $order = $this->order(['user_id' => $customer->id]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 10000, 'total' => 10000]);

        foreach ([$product, $product->category] as $referenced) {
            try {
                $referenced->forceDelete();
                $this->fail(class_basename($referenced) . ' was deleted for real while still referenced');
            } catch (QueryException) {
                $this->assertNotNull($referenced->fresh());
            }
        }

        // An order outlives its customer's account: it has its own copy of the name, phone and address
        $customer->forceDelete();
        $this->assertNull($order->fresh()->user_id);
        $this->assertSame(1, OrderItem::count());
    }

    public function test_a_deleted_order_still_answers_a_retry_of_its_checkout(): void
    {
        $product = $this->product();
        $checkout = fn () => $this->withHeaders(['Idempotency-Key' => 'retry-1'])->postJson('/api/checkout', [
            'full_name' => 'Guest', 'phone_number' => '01000000000', 'city' => 'Cairo', 'state' => 'Cairo',
            'shipping_way' => 'home', 'payment_method' => 'cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $orderId = $checkout()->assertCreated()->json('data.id');
        Order::find($orderId)->delete();

        $checkout()->assertOk()->assertJsonPath('data.id', $orderId);
        $this->assertSame(1, Order::withTrashed()->count());
        $this->assertSame(49, (int) $product->fresh()->stock);
    }

    // ── coupons and accounts can be created again ──────────────────────

    public function test_a_deleted_coupon_stops_working_and_its_code_can_be_used_again(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();
        $coupon = Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10]);
        $form = ['code' => 'SAVE10', 'type' => 'percent', 'value' => 20];

        // While it exists the code is taken
        $this->actingAs($admin)->post(route('admin.coupons.store'), $form)->assertSessionHasErrors('code');

        $this->actingAs($admin)->delete(route('admin.coupons.destroy', $coupon))->assertRedirect();

        $this->actingAs($customer)->postJson('/api/coupons/apply', ['code' => 'SAVE10', 'cart_total' => 100])->assertNotFound();
        $this->actingAs($customer)->postJson('/api/cart', ['product_id' => $this->product()->id, 'quantity' => 1])->assertOk();
        $this->actingAs($customer)->postJson('/api/checkout', [
            'full_name' => 'C', 'phone_number' => '01000000000', 'city' => 'Cairo', 'state' => 'Cairo',
            'shipping_way' => 'home', 'payment_method' => 'cash', 'coupon_code' => 'SAVE10',
        ])->assertStatus(422);
        $this->assertSame(0, Order::count());

        $this->actingAs($admin)->post(route('admin.coupons.store'), $form)->assertSessionHasNoErrors();

        $this->assertSame(20, (int) Coupon::sole()->value);
        $this->assertSame(2, Coupon::withTrashed()->where('code', 'SAVE10')->count());
        $this->actingAs($customer)->postJson('/api/coupons/apply', ['code' => 'SAVE10', 'cart_total' => 100])
            ->assertOk()->assertJsonPath('data.discount_amount', 20);
    }

    public function test_a_deleted_account_is_locked_out_and_its_email_can_register_again(): void
    {
        $admin = $this->admin();
        $old = User::factory()->create(['email' => 'mona@example.com', 'password' => 'old-password']);
        $registration = ['name' => 'Mona', 'email' => 'mona@example.com', 'password' => 'new-password', 'password_confirmation' => 'new-password', 'phone_number' => '01000000000'];

        $this->postJson('/api/register', $registration)->assertStatus(422)->assertJsonValidationErrors('email');

        $this->actingAs($admin)->delete(route('admin.users.destroy', $old))->assertRedirect();
        auth()->forgetGuards();

        $this->postJson('/api/login', ['identifier' => 'mona@example.com', 'password' => 'old-password'])->assertUnauthorized();
        $this->postJson('/api/password/forgot', ['email' => 'mona@example.com'])->assertStatus(422);

        $newId = $this->postJson('/api/register', $registration)->assertCreated()->json('data.user.id');
        $this->assertNotSame($old->id, $newId);

        $this->postJson('/api/login', ['identifier' => 'mona@example.com', 'password' => 'new-password'])
            ->assertOk()->assertJsonPath('data.user.id', $newId);
        $this->postJson('/api/login', ['identifier' => 'mona@example.com', 'password' => 'old-password'])->assertUnauthorized();
        $this->postJson('/api/register', $registration)->assertStatus(422)->assertJsonValidationErrors('email');
    }

    // ── admin-only API routes ──────────────────────────────────────────

    public function test_only_admins_manage_coupons_and_order_status_through_the_api(): void
    {
        $customer = User::factory()->create();
        $coupon = Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10]);
        $order = $this->order(['user_id' => $customer->id]);
        $newCoupon = ['code' => 'FREE', 'type' => 'percent', 'value' => 100];

        $this->actingAs($customer)->getJson('/api/coupons')->assertForbidden();
        $this->actingAs($customer)->getJson("/api/coupons/{$coupon->id}")->assertForbidden();
        $this->actingAs($customer)->postJson('/api/coupons', $newCoupon)->assertForbidden();
        $this->actingAs($customer)->putJson("/api/coupons/{$coupon->id}", ['value' => 100])->assertForbidden();
        $this->actingAs($customer)->deleteJson("/api/coupons/{$coupon->id}")->assertForbidden();
        $this->actingAs($customer)->patchJson("/api/orders/{$order->id}/status", ['status' => 'delivered'])->assertForbidden();

        $this->assertSame(10, (int) $coupon->fresh()->value);
        $this->assertSame(1, Coupon::count());
        $this->assertSame('pending', $order->fresh()->status);

        // Customers keep what they need: applying a coupon and cancelling their own pending order
        $this->actingAs($customer)->postJson('/api/coupons/apply', ['code' => 'SAVE10', 'cart_total' => 100])->assertOk();
        $this->actingAs($customer)->postJson("/api/orders/{$order->id}/cancel")->assertOk();

        $admin = $this->admin();
        $this->actingAs($admin)->getJson('/api/coupons')->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($admin)->postJson('/api/coupons', $newCoupon)->assertCreated();
        $this->actingAs($admin)->patchJson("/api/orders/{$order->id}/status", ['status' => 'processing'])->assertOk();
        $this->assertSame('processing', $order->fresh()->status);
    }

    // ── migration ──────────────────────────────────────────────────────

    public function test_soft_delete_migration_rolls_back_and_refuses_when_rows_were_deleted(): void
    {
        $migration = require database_path('migrations/2026_10_02_000000_add_soft_deletes_to_remaining_tables.php');
        $coupon = Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10]);
        $coupon->delete();
        Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 20]);

        try {
            $migration->down();
            $this->fail('Rolled back although a deleted coupon would have come back');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('coupons: 1 deleted row(s)', $e->getMessage());
            $this->assertStringContainsString('coupons.code: used more than once (SAVE10)', $e->getMessage());
        }
        $this->assertSoftDeleted($coupon);

        $coupon->forceDelete();
        $migration->down();
        $this->assertFalse(\Schema::hasColumn('orders', 'deleted_at'));
        $this->assertFalse(\Schema::hasColumn('coupons', 'deleted_at'));

        $migration->up();
        $this->assertTrue(\Schema::hasColumn('orders', 'deleted_at'));
        $this->assertSame(20, (int) Coupon::sole()->value);
    }
}
