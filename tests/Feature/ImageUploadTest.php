<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\ImageUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Every upload is stored as a resized WebP through ImageUploader; images:convert-webp converts the old ones.
 */
class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function productForm(array $extra = []): array
    {
        return array_merge([
            'category_id' => Category::factory()->create()->id,
            'name_ar' => 'منتج', 'name_en' => 'Product',
            'description_ar' => 'وصف', 'description_en' => 'Description',
            'price' => 100, 'stock' => 5, 'status' => '1',
        ], $extra);
    }

    /** A real PNG on the public disk, as uploads used to be stored. */
    private function storedPng(string $path, int $width = 800, int $height = 600): string
    {
        $file = UploadedFile::fake()->image('x.png', $width, $height); // its temp file lives as long as $file
        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    private function assertWebp(string $path): void
    {
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('image/webp', Storage::disk('public')->mimeType($path));
    }

    public function test_admin_product_image_is_stored_as_webp(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->productForm(['image' => UploadedFile::fake()->image('big.png', 1200, 900)]))
            ->assertRedirect()->assertSessionHasNoErrors();

        $product = Product::sole();
        $this->assertStringStartsWith('uploads/products/', $product->image);
        $this->assertWebp($product->image);
    }

    public function test_large_images_are_shrunk_to_the_max_dimension_and_small_ones_are_not_enlarged(): void
    {
        $uploader = app(ImageUploader::class);

        $big = $uploader->store(UploadedFile::fake()->image('big.jpg', 3000, 2000), 'tests');
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($big));
        $this->assertSame([ImageUploader::MAX_DIMENSION, 1280], [$width, $height]);

        $small = $uploader->store(UploadedFile::fake()->image('small.png', 300, 200), 'tests');
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($small));
        $this->assertSame([300, 200], [$width, $height]);
    }

    public function test_gif_uploads_are_kept_as_they_are(): void
    {
        $path = app(ImageUploader::class)->store(UploadedFile::fake()->image('anim.gif', 100, 100), 'tests');

        $this->assertStringEndsWith('.gif', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_a_product_image_deletes_the_old_file(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create(['image' => $this->storedPng('uploads/products/old.png')]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productForm(['image' => UploadedFile::fake()->image('new.png')]))
            ->assertRedirect()->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('uploads/products/old.png');
        $this->assertWebp($product->refresh()->image);
    }

    public function test_updating_without_a_new_image_keeps_the_current_one(): void
    {
        $product = Product::factory()->create(['image' => $this->storedPng('uploads/products/keep.png')]);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), $this->productForm())
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('uploads/products/keep.png', $product->refresh()->image);
    }

    public function test_extra_product_images_are_stored_as_webp(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.products.images.store', $product), [
                'images' => [UploadedFile::fake()->image('a.png'), UploadedFile::fake()->image('b.jpg')],
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertCount(2, $product->extraImages);
        $product->extraImages->each(fn (ProductImage $image) => $this->assertWebp($image->image));
    }

    public function test_offer_image_is_stored_as_webp_and_replacing_it_deletes_the_old_file(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create(['price' => 10000, 'discount_price' => 0]);
        $form = [
            'name_ar' => 'عرض', 'name_en' => 'Offer', 'type' => 'bundle', 'bundle_price' => 150, 'is_active' => '1',
            'products' => [$product->id], 'quantities' => [$product->id => 2],
        ];

        $this->actingAs($admin)
            ->post(route('admin.offers.store'), $form + ['image' => UploadedFile::fake()->image('offer.png', 1600, 900)])
            ->assertRedirect()->assertSessionHasNoErrors();

        $offer = Offer::sole();
        $this->assertStringStartsWith('offers/', $offer->image);
        $this->assertWebp($offer->image);
        $old = $offer->image;

        $this->actingAs($admin)
            ->put(route('admin.offers.update', $offer), $form + ['image' => UploadedFile::fake()->image('new.jpg')])
            ->assertRedirect()->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($old);
        $this->assertWebp($offer->refresh()->image);
    }

    public function test_banner_image_is_stored_as_webp_and_kept_when_updating_without_a_new_one(): void
    {
        $admin = $this->admin();
        $form = ['title_ar' => 'بانر', 'title_en' => 'Banner', 'status' => '1'];

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), $form + ['image' => UploadedFile::fake()->image('hero.png', 2400, 900)])
            ->assertRedirect()->assertSessionHasNoErrors();

        $banner = Banner::sole();
        $this->assertStringStartsWith('uploads/banners/', $banner->image);
        $this->assertWebp($banner->image);
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($banner->image));
        $this->assertSame([ImageUploader::MAX_DIMENSION, 720], [$width, $height]);

        $image = $banner->image;
        $this->actingAs($admin)->put(route('admin.banners.update', $banner), $form)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($image, $banner->refresh()->image);
    }

    public function test_uploads_up_to_10mb_are_accepted(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->productForm(['image' => UploadedFile::fake()->image('huge.png')->size(9000)]))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->productForm(['image' => UploadedFile::fake()->image('too-big.png')->size(11000)]))
            ->assertSessionHasErrors('image');
    }

    public function test_convert_command_converts_stored_images_and_keeps_the_originals(): void
    {
        $product = Product::factory()->create(['image' => $this->storedPng('uploads/products/p.png', 2400, 1200)]);
        $deleted = Product::factory()->create(['image' => $this->storedPng('uploads/products/d.png')]);
        $deleted->delete();
        $webp = Product::factory()->create(['image' => 'uploads/products/already.webp']);
        $missing = Product::factory()->create(['image' => 'uploads/products/gone.png']);

        $logsBefore = glob(storage_path('logs/webp-conversion-*.csv'));
        $this->artisan('images:convert-webp')->assertSuccessful();

        // The old → new log lists both converted rows; remove it so test runs leave nothing behind.
        $logs = array_diff(glob(storage_path('logs/webp-conversion-*.csv')), $logsBefore);
        $this->assertCount(1, $logs);
        $log = file_get_contents(reset($logs));
        array_map('unlink', $logs);
        $this->assertStringContainsString('uploads/products/p.png,uploads/products/p.webp', $log);
        $this->assertStringContainsString('uploads/products/d.png,uploads/products/d.webp', $log);

        $this->assertSame('uploads/products/p.webp', $product->refresh()->image);
        $this->assertWebp($product->image);
        [$width] = getimagesizefromstring(Storage::disk('public')->get($product->image));
        $this->assertSame(ImageUploader::MAX_DIMENSION, $width);
        Storage::disk('public')->assertExists('uploads/products/p.png');

        $this->assertSame('uploads/products/d.webp', $deleted->refresh()->image);
        $this->assertSame('uploads/products/already.webp', $webp->refresh()->image);
        $this->assertSame('uploads/products/gone.png', $missing->refresh()->image);
    }

    public function test_convert_command_dry_run_changes_nothing(): void
    {
        $product = Product::factory()->create(['image' => $this->storedPng('uploads/products/p.png')]);

        $this->artisan('images:convert-webp', ['--dry-run' => true])
            ->expectsOutputToContain('1 image(s) would be converted')
            ->assertSuccessful();

        $this->assertSame('uploads/products/p.png', $product->refresh()->image);
        Storage::disk('public')->assertMissing('uploads/products/p.webp');
    }
}
