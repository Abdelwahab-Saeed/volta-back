<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingLocationTest extends TestCase
{
    use RefreshDatabase;

    private function setLocation(?string $arabic, ?string $english): void
    {
        Setting::updateOrCreate(['key' => 'location_ar'], ['value' => $arabic]);
        Setting::updateOrCreate(['key' => 'location_en'], ['value' => $english]);
    }

    public function test_migration_treats_existing_location_as_english(): void
    {
        $migration = require database_path('migrations/2026_09_15_000001_split_location_setting.php');
        $migration->down();

        DB::table('settings')->where('key', 'location')->update(['value' => 'Cairo, Egypt']);

        $migration->up();

        $this->assertDatabaseMissing('settings', ['key' => 'location']);
        $this->assertDatabaseHas('settings', ['key' => 'location_en', 'value' => 'Cairo, Egypt']);
        $this->assertDatabaseHas('settings', ['key' => 'location_ar', 'value' => null]);
    }

    public function test_migration_keeps_an_english_location_that_is_already_filled(): void
    {
        $migration = require database_path('migrations/2026_09_15_000001_split_location_setting.php');
        $migration->down();

        DB::table('settings')->where('key', 'location')->update(['value' => 'Cairo, Egypt']);
        $this->setLocation('بني سويف - مصر', 'Beni-suef - Egypt');

        $migration->up();

        $this->assertDatabaseMissing('settings', ['key' => 'location']);
        $this->assertDatabaseHas('settings', ['key' => 'location_en', 'value' => 'Beni-suef - Egypt']);
        $this->assertDatabaseHas('settings', ['key' => 'location_ar', 'value' => 'بني سويف - مصر']);
    }

    public function test_api_returns_location_in_request_language(): void
    {
        $this->setLocation('القاهرة، مصر', 'Cairo, Egypt');

        $this->getJson('/api/settings', ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertJson([
                'location' => 'Cairo, Egypt',
                'location_ar' => 'القاهرة، مصر',
                'location_en' => 'Cairo, Egypt',
            ]);

        $this->getJson('/api/settings', ['Accept-Language' => 'ar'])
            ->assertJson(['location' => 'القاهرة، مصر']);

        $this->getJson('/api/settings?lang=en', ['Accept-Language' => 'ar'])
            ->assertJson(['location' => 'Cairo, Egypt']);
    }

    public function test_api_location_falls_back_to_the_other_language(): void
    {
        $this->setLocation(null, 'Cairo, Egypt');

        $this->getJson('/api/settings', ['Accept-Language' => 'ar'])
            ->assertJson(['location' => 'Cairo, Egypt', 'location_ar' => null]);
    }

    public function test_admin_can_save_both_locations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('name="location_ar"', false)
            ->assertSee('name="location_en"', false);

        $this->actingAs($admin)
            ->post(route('admin.settings.store'), [
                'location_ar' => 'الجيزة، مصر',
                'location_en' => 'Giza, Egypt',
            ])
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'location_ar', 'value' => 'الجيزة، مصر']);
        $this->assertDatabaseHas('settings', ['key' => 'location_en', 'value' => 'Giza, Egypt']);
    }
}
