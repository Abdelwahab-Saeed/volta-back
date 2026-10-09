<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
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

    private function form(array $extra = []): array
    {
        return array_merge(['title_ar' => 'بانر', 'title_en' => 'Banner', 'status' => '1'], $extra);
    }

    public function test_redirect_url_is_saved_changed_and_cleared_by_the_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), $this->form([
                'image' => UploadedFile::fake()->image('hero.png', 1600, 600),
                'redirect_url' => 'https://example.com/sale?utm=banner',
            ]))
            ->assertRedirect()->assertSessionHasNoErrors();

        $banner = Banner::sole();
        $this->assertSame('https://example.com/sale?utm=banner', $banner->redirect_url);

        $this->actingAs($admin)->put(route('admin.banners.update', $banner), $this->form(['redirect_url' => '/offers/3']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('/offers/3', $banner->refresh()->redirect_url);

        $this->actingAs($admin)->put(route('admin.banners.update', $banner), $this->form(['redirect_url' => '']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($banner->refresh()->redirect_url);
    }

    public function test_redirect_url_must_be_a_web_link_or_a_store_path(): void
    {
        $admin = $this->admin();
        $banner = Banner::create(['title_ar' => 'ب', 'title_en' => 'B', 'image' => 'uploads/banners/b.webp', 'status' => true]);

        foreach (['javascript:alert(1)', 'mailto:a@b.com', '//evil.example', '/\\evil.example', 'offers/3', 'https://', 'https://exa mple.com'] as $bad) {
            $this->actingAs($admin)->put(route('admin.banners.update', $banner), $this->form(['redirect_url' => $bad]))
                ->assertSessionHasErrors('redirect_url');
        }

        $this->assertNull($banner->refresh()->redirect_url);
    }

    public function test_banners_api_returns_the_redirect_url(): void
    {
        Banner::create(['title_ar' => 'أ', 'title_en' => 'A', 'image' => 'a.webp', 'status' => true, 'redirect_url' => '/products/7']);
        Banner::create(['title_ar' => 'ب', 'title_en' => 'B', 'image' => 'b.webp', 'status' => true]);

        $response = $this->getJson('/api/banners')->assertOk()->assertJsonCount(2, 'data');

        $this->assertEqualsCanonicalizing(['/products/7', null], array_column($response->json('data'), 'redirect_url'));
    }

    public function test_mobile_image_is_optional_stored_kept_and_removable(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), $this->form(['image' => UploadedFile::fake()->image('desktop.png', 1920, 600)]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $banner = Banner::sole();
        $this->assertNull($banner->image_mobile);

        $this->actingAs($admin)
            ->put(route('admin.banners.update', $banner), $this->form(['image_mobile' => UploadedFile::fake()->image('phone.png', 1080, 630)]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $mobile = $banner->refresh()->image_mobile;
        $this->assertStringStartsWith('uploads/banners/', $mobile);
        $this->assertStringEndsWith('.webp', $mobile);
        Storage::disk('public')->assertExists($mobile);

        // Saving without a new file keeps it
        $this->actingAs($admin)->put(route('admin.banners.update', $banner), $this->form())->assertSessionHasNoErrors();
        $this->assertSame($mobile, $banner->refresh()->image_mobile);

        $this->getJson('/api/banners')->assertOk()->assertJsonPath('data.0.image_mobile', $mobile);

        $this->actingAs($admin)->put(route('admin.banners.update', $banner), $this->form(['remove_image_mobile' => '1']))->assertSessionHasNoErrors();
        $this->assertNull($banner->refresh()->image_mobile);
        Storage::disk('public')->assertMissing($mobile);
        $this->assertNotNull($banner->image);
    }

    public function test_mobile_image_must_be_an_image(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.banners.store'), $this->form([
                'image' => UploadedFile::fake()->image('desktop.png', 1920, 600),
                'image_mobile' => UploadedFile::fake()->create('phone.pdf', 10, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('image_mobile');

        $this->assertSame(0, Banner::count());
    }

    public function test_banner_form_explains_both_images_and_shows_previews_and_the_link_field(): void
    {
        $banner = Banner::create(['title_ar' => 'ب', 'title_en' => 'B', 'image' => 'uploads/banners/b.webp', 'status' => true, 'redirect_url' => 'https://example.com']);

        $this->actingAs($this->admin())->get(route('admin.banners.edit', $banner))
            ->assertOk()
            ->assertSee('1920×600')
            ->assertSee('1080×630')
            ->assertSee('id="image-preview"', false)
            ->assertSee('id="image_mobile-preview"', false)
            ->assertSee('data-size="1920x600"', false)
            ->assertSee('data-size="1080x630"', false)
            ->assertSee('onchange="pickImage(this)"', false)
            ->assertDontSee('name="remove_image_mobile"', false) // nothing to remove yet
            ->assertSee('name="redirect_url"', false)
            ->assertSee('value="https://example.com"', false);

        $this->actingAs($this->admin())->get(route('admin.banners.index'))->assertOk()->assertSee('بدون صورة موبايل');
    }
}
