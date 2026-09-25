<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            // Active
            ['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'min_order_amount' => 300, 'starts_at' => now()->subMonth(), 'expires_at' => now()->addMonths(3), 'max_uses' => 500, 'times_used' => 0],
            ['code' => 'VOLTA50', 'type' => 'fixed', 'value' => 50, 'min_order_amount' => 500, 'starts_at' => now()->subWeeks(2), 'expires_at' => now()->addMonth(), 'max_uses' => 200, 'times_used' => 0],
            ['code' => 'TECH15', 'type' => 'percent', 'value' => 15, 'min_order_amount' => 1500, 'starts_at' => null, 'expires_at' => null, 'max_uses' => null, 'times_used' => 0],
            // Expired
            ['code' => 'SUMMER20', 'type' => 'percent', 'value' => 20, 'min_order_amount' => 1000, 'starts_at' => now()->subMonths(4), 'expires_at' => now()->subMonth(), 'max_uses' => null, 'times_used' => 0],
            // Usage limit reached
            ['code' => 'FLASH100', 'type' => 'fixed', 'value' => 100, 'min_order_amount' => 1200, 'starts_at' => now()->subWeek(), 'expires_at' => now()->addWeek(), 'max_uses' => 10, 'times_used' => 10],
            // Not started yet
            ['code' => 'BLACKFRIDAY25', 'type' => 'percent', 'value' => 25, 'min_order_amount' => 800, 'starts_at' => now()->addMonths(2), 'expires_at' => now()->addMonths(2)->addWeek(), 'max_uses' => 1000, 'times_used' => 0],
        ];

        foreach ($coupons as $coupon) {
            // Amounts above are in pounds.
            Coupon::updateOrCreate(['code' => $coupon['code']], Coupon::fromInput($coupon));
        }
    }
}
