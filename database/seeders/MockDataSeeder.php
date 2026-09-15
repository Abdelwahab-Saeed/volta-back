<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeds mock store data around the existing users (it never creates users).
 *
 * php artisan db:seed --class=MockDataSeeder
 */
class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        $mockCategories = array_column(CategorySeeder::CATEGORIES, 'name_en');

        if (Category::withTrashed()->whereIn('name_en', $mockCategories)->exists()) {
            $this->command->warn('Mock data is already seeded, skipping to avoid duplicates.');

            return;
        }

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            BannerSeeder::class,
            PostSeeder::class,
            CouponSeeder::class,
            AddressSeeder::class,
            OrderSeeder::class,
            ShoppingActivitySeeder::class,
        ]);
    }
}
