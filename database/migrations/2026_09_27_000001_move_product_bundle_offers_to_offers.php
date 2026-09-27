<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retires the old per-product "N units for a fixed price" offers (product_bundle_offers):
 * each one becomes a single-product bundle offer, e.g. "3 × A for 250".
 *
 * product_bundle_offers itself is NOT dropped here; a later migration drops it once production is verified.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $t) {
            // Which old offer this came from, for tracing and for rollback. No FK: the old table goes away later.
            $t->unsignedBigInteger('legacy_bundle_offer_id')->nullable()->unique()->after('is_active');
        });

        $legacyOffers = DB::table('product_bundle_offers as b')
            ->join('products as p', 'p.id', '=', 'b.product_id')
            ->select('b.*', 'p.name_ar', 'p.name_en', 'p.deleted_at as product_deleted_at')
            ->orderBy('b.id')
            ->get();

        foreach ($legacyOffers as $legacy) {
            $offerId = DB::table('offers')->insertGetId([
                'name_ar' => "{$legacy->quantity} قطع من {$legacy->name_ar}",
                'name_en' => "{$legacy->quantity} × {$legacy->name_en}",
                'type' => 'bundle',
                'bundle_price' => $legacy->bundle_price,
                'get_discount_percent' => 100,
                'is_active' => $legacy->is_active && $legacy->product_deleted_at === null,
                'legacy_bundle_offer_id' => $legacy->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('offer_product')->insert([
                'offer_id' => $offerId,
                'product_id' => $legacy->product_id,
                'quantity' => $legacy->quantity,
            ]);
        }

        // Cart lines priced by an old offer carry a reduced unit price; reset every snapshot to the product's own price,
        // which is what checkout charges from now on.
        DB::table('cart_items')->update([
            'price_snapshot' => DB::raw(
                '(SELECT CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END FROM products p WHERE p.id = cart_items.product_id)'
            ),
        ]);
    }

    public function down(): void
    {
        $offerIds = DB::table('offers')->whereNotNull('legacy_bundle_offer_id')->pluck('id');

        DB::table('offer_product')->whereIn('offer_id', $offerIds)->delete();
        DB::table('offers')->whereIn('id', $offerIds)->delete();

        Schema::table('offers', function (Blueprint $t) {
            $t->dropUnique(['legacy_bundle_offer_id']);
            $t->dropColumn('legacy_bundle_offer_id');
        });

        // Cart price snapshots are not restored: the old code recomputes them on the next cart change,
        // and checkout always recalculated prices anyway.
    }
};
