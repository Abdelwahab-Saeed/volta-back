<?php

namespace Tests\Feature;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_validate_valid_coupon()
    {
        $coupon = Coupon::create([
            'code' => 'TEST10',
            'type' => 'percent',
            'value' => 10,
        ]);

        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/coupons/apply', [
            'code' => 'TEST10',
            'cart_total' => 100,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['data' => ['discount_amount' => 10]]);
    }

    public function test_cannot_validate_expired_coupon()
    {
        $coupon = Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'fixed',
            'value' => 10,
            'expires_at' => Carbon::yesterday(),
        ]);

        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/coupons/apply', [
            'code' => 'EXPIRED',
            'cart_total' => 100,
        ]);

        $response->assertStatus(422);
    }
}
