<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Offers are now bought directly from the offer page (never mixed with the cart), so only the
 * package-shaped types stay:
 *   - bundle:       fixed price for a set of products, each with its own quantity ("3 × A for 250", "A + B for 150")
 *   - buy_x_get_y:  buy X units, get Y more at N% off (100 = free), or get a different gift product
 * Cart-wide discounts ("spend X get Y", % or amount off) are coupons.
 */
return new class extends Migration
{
    private const REMOVED_TYPES = ['percentage', 'fixed', 'spend_x_get_y'];

    public function up(): void
    {
        $this->guard();

        Schema::table('offers', function (Blueprint $t) {
            $t->dropColumn(['value', 'min_spend', 'discount_amount']);
        });
        Schema::table('offers', function (Blueprint $t) {
            $t->enum('type', ['bundle', 'buy_x_get_y'])->change();
        });

        Schema::table('offer_product', function (Blueprint $t) {
            $t->unsignedSmallInteger('quantity')->default(1); // units of this product per bundle set
        });

        Schema::table('orders', function (Blueprint $t) {
            // What the offer looked like when it was bought, so the order stays readable after the offer is edited or deleted.
            $t->json('offer_snapshot')->nullable()->after('offer_discount');
            // Sent by the client per checkout attempt; a retry with the same key returns the same order.
            $t->string('idempotency_key', 64)->nullable()->unique()->after('offer_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropUnique(['idempotency_key']);
            $t->dropColumn(['offer_snapshot', 'idempotency_key']);
        });

        Schema::table('offer_product', fn (Blueprint $t) => $t->dropColumn('quantity'));

        Schema::table('offers', function (Blueprint $t) {
            $t->enum('type', ['percentage', 'fixed', 'bundle', 'buy_x_get_y', 'spend_x_get_y'])->change();
        });
        Schema::table('offers', function (Blueprint $t) {
            $t->bigInteger('value')->nullable()->after('type');
            $t->bigInteger('min_spend')->nullable()->after('get_product_id');
            $t->bigInteger('discount_amount')->nullable()->after('min_spend');
        });
    }

    /**
     * Offers of the removed types cannot be kept. Stop and name them instead of deleting anything.
     */
    private function guard(): void
    {
        $ids = DB::table('offers')->whereIn('type', self::REMOVED_TYPES)->pluck('id');

        if ($ids->isNotEmpty()) {
            throw new RuntimeException(
                'Offers of removed types (percentage, fixed, spend_x_get_y) must be recreated as coupons and deleted first: ids '
                . $ids->implode(', ')
            );
        }
    }
};
