<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The offers tables were written in pounds before money moved to integer piasters.
 * Converts them to piasters, and adds get_discount_percent so "buy X, get Y at N% off" is possible
 * (100 = free, 50 = half price).
 */
return new class extends Migration
{
    private const MONEY_COLUMNS = ['min_spend', 'discount_amount', 'bundle_price'];

    public function up(): void
    {
        $this->guard();

        // Widen first so multiplying by 100 cannot overflow decimal(10,2).
        Schema::table('offers', function (Blueprint $t) {
            foreach (array_merge(['value'], self::MONEY_COLUMNS) as $column) {
                $t->decimal($column, 14, 2)->nullable()->change();
            }
        });

        $update = ['value' => DB::raw("CASE WHEN {$this->wrap('type')} = 'fixed' THEN ROUND({$this->wrap('value')} * 100) ELSE ROUND({$this->wrap('value')}) END")];
        foreach (self::MONEY_COLUMNS as $column) {
            $update[$column] = DB::raw("ROUND({$this->wrap($column)} * 100)");
        }
        DB::table('offers')->update($update);

        Schema::table('offers', function (Blueprint $t) {
            foreach (array_merge(['value'], self::MONEY_COLUMNS) as $column) {
                $t->bigInteger($column)->nullable()->change();
            }
            $t->unsignedTinyInteger('get_discount_percent')->default(100)->after('get_quantity');
        });

        // Every write to this column since the money migration already went through MoneyCast (piasters),
        // so only the type changes. guard() makes sure there are no rows whose unit is unknown.
        Schema::table('orders', fn (Blueprint $t) => $t->bigInteger('offer_discount')->default(0)->change());
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->decimal('offer_discount', 10, 2)->default(0)->change());

        Schema::table('offers', function (Blueprint $t) {
            $t->dropColumn('get_discount_percent');
            foreach (array_merge(['value'], self::MONEY_COLUMNS) as $column) {
                $t->decimal($column, 14, 2)->nullable()->change();
            }
        });

        $update = ['value' => DB::raw("CASE WHEN {$this->wrap('type')} = 'fixed' THEN {$this->wrap('value')} / 100.0 ELSE {$this->wrap('value')} END")];
        foreach (self::MONEY_COLUMNS as $column) {
            $update[$column] = DB::raw("{$this->wrap($column)} / 100.0");
        }
        DB::table('offers')->update($update);

        Schema::table('offers', function (Blueprint $t) {
            foreach (array_merge(['value'], self::MONEY_COLUMNS) as $column) {
                $t->decimal($column, 10, 2)->nullable()->change();
            }
        });
    }

    /**
     * Refuse to guess: stop with a clear message instead of converting data whose unit is ambiguous.
     */
    private function guard(): void
    {
        $fractionalPercent = DB::table('offers')
            ->where('type', '!=', 'fixed')
            ->whereNotNull('value')
            ->whereRaw("{$this->wrap('value')} <> ROUND({$this->wrap('value')})")
            ->pluck('id');

        if ($fractionalPercent->isNotEmpty()) {
            throw new RuntimeException(
                'Offers with fractional percentages cannot be converted to whole percentages: ids ' . $fractionalPercent->implode(', ')
            );
        }

        $ordersWithOfferDiscount = DB::table('orders')->where('offer_discount', '!=', 0)->count();

        if ($ordersWithOfferDiscount > 0) {
            throw new RuntimeException(
                "{$ordersWithOfferDiscount} order(s) already have offer_discount set; their unit (pounds or piasters) must be checked by hand before converting."
            );
        }
    }

    private function wrap(string $column): string
    {
        return DB::connection()->getQueryGrammar()->wrap($column);
    }
};
