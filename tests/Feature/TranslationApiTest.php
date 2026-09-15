<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::factory()->create([
            'name_ar' => 'منظم جهد',
            'name_en' => 'Voltage Stabilizer',
            'description_ar' => 'وصف عربي',
            'description_en' => 'English description',
            ...$attributes,
        ]);
    }

    public function test_migration_copies_existing_text_into_both_languages(): void
    {
        $migration = require database_path('migrations/2026_09_15_000000_split_translatable_columns.php');
        $migration->down();

        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'منظمات',
            'description' => 'Mixed description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('products')->insert([
            'category_id' => $categoryId,
            'name' => 'Voltage Stabilizer',
            'description' => null,
            'price' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration->up();

        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
            'name_ar' => 'منظمات',
            'name_en' => 'منظمات',
            'description_ar' => 'Mixed description',
            'description_en' => 'Mixed description',
        ]);
        $this->assertDatabaseHas('products', [
            'name_ar' => 'Voltage Stabilizer',
            'name_en' => 'Voltage Stabilizer',
            'description_ar' => null,
            'description_en' => null,
        ]);
        $this->assertFalse(Schema::hasColumn('products', 'name'));
        $this->assertFalse(Schema::hasColumn('categories', 'description'));
    }

    public function test_product_fields_follow_accept_language(): void
    {
        $product = $this->product();

        $this->getJson("/api/products/{$product->id}", ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Voltage Stabilizer')
            ->assertJsonPath('data.description', 'English description')
            ->assertJsonPath('data.name_ar', 'منظم جهد')
            ->assertJsonPath('data.name_en', 'Voltage Stabilizer')
            ->assertJsonPath('data.description_ar', 'وصف عربي')
            ->assertJsonPath('data.description_en', 'English description');

        $this->getJson("/api/products/{$product->id}", ['Accept-Language' => 'ar'])
            ->assertJsonPath('data.name', 'منظم جهد')
            ->assertJsonPath('data.description', 'وصف عربي');
    }

    public function test_locale_resolution_handles_real_browser_headers(): void
    {
        $url = "/api/products/{$this->product()->id}";

        $this->getJson($url, ['Accept-Language' => 'en-US,en;q=0.9'])->assertJsonPath('data.name', 'Voltage Stabilizer');
        $this->getJson($url, ['Accept-Language' => 'ar-EG,ar;q=0.9,en;q=0.8'])->assertJsonPath('data.name', 'منظم جهد');
        // Unsupported languages fall back to the default (Arabic). A request with
        // no header can't be simulated: the test client always sends "en-us".
        $this->getJson($url, ['Accept-Language' => 'fr'])->assertJsonPath('data.name', 'منظم جهد');
        $this->getJson("{$url}?lang=en", ['Accept-Language' => 'ar'])->assertJsonPath('data.name', 'Voltage Stabilizer');
    }

    public function test_api_responses_vary_on_accept_language(): void
    {
        $response = $this->getJson('/api/categories');

        $this->assertContains('Accept-Language', array_map('trim', explode(',', implode(',', $response->headers->all('vary')))));
    }

    public function test_empty_translation_falls_back_to_the_other_language(): void
    {
        $product = $this->product(['name_en' => null]);

        $this->getJson("/api/products/{$product->id}", ['Accept-Language' => 'en'])
            ->assertJsonPath('data.name', 'منظم جهد')
            ->assertJsonPath('data.name_en', null);
    }

    public function test_category_and_feature_payloads_include_both_languages(): void
    {
        $category = Category::factory()->create(['name_ar' => 'منظمات', 'name_en' => 'Stabilizers']);
        $product = $this->product(['category_id' => $category->id]);
        $product->features()->create(['name_ar' => 'حماية', 'name_en' => 'Protection']);

        $this->getJson('/api/categories', ['Accept-Language' => 'en'])
            ->assertJsonPath('data.0.name', 'Stabilizers')
            ->assertJsonPath('data.0.name_ar', 'منظمات')
            ->assertJsonPath('data.0.name_en', 'Stabilizers');

        $this->getJson("/api/products/{$product->id}", ['Accept-Language' => 'en'])
            ->assertJsonPath('data.category.name', 'Stabilizers')
            ->assertJsonPath('data.features.0.name', 'Protection')
            ->assertJsonPath('data.features.0.name_ar', 'حماية')
            ->assertJsonPath('data.features.0.name_en', 'Protection');
    }

    public function test_search_matches_both_languages(): void
    {
        $this->product();
        $this->product(['name_ar' => 'كابل', 'name_en' => 'Cable', 'description_ar' => 'سلك', 'description_en' => 'Wire']);

        foreach (['Stabilizer', 'منظم'] as $term) {
            $this->getJson('/api/products?'.http_build_query(['search' => $term]))
                ->assertOk()
                ->assertJsonCount(1, 'data.items')
                ->assertJsonPath('data.items.0.name_en', 'Voltage Stabilizer');
        }
    }

    public function test_raw_model_payloads_are_localized(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $user->wishlist()->attach($product->id);

        $this->actingAs($user)->getJson('/api/wishlist', ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Voltage Stabilizer')
            ->assertJsonPath('data.0.name_ar', 'منظم جهد')
            ->assertJsonPath('data.0.category.name', $product->category->name_en);

        $this->actingAs($user)->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1], ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertJsonPath('data.items.0.product.name', 'Voltage Stabilizer')
            ->assertJsonPath('data.items.0.product.name_ar', 'منظم جهد');
    }

    public function test_admin_category_form_requires_both_languages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name_ar' => 'منظمات'])
            ->assertSessionHasErrors('name_en');

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name_ar' => 'منظمات', 'name_en' => 'Stabilizers'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['name_ar' => 'منظمات', 'name_en' => 'Stabilizers']);
    }
}
