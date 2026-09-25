<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Store money as integer piasters instead of decimal pounds.
 *
 * Nothing is dropped: every old column is renamed to "{column}_legacy" and kept as a backup.
 * A later migration drops the legacy columns once production has been verified.
 */
return new class extends Migration
{
    /**
     * table => [column => [nullable, default in pounds]]
     */
    private const COLUMNS = [
        'products' => [
            'price' => [false, null],
            'discount_price' => [true, null],
            'cost_price' => [true, null],
            'shipping_cost' => [false, 0],
        ],
        'product_bundle_offers' => [
            'bundle_price' => [false, null],
        ],
        'cart_items' => [
            'price_snapshot' => [true, null],
        ],
        'orders' => [
            'subtotal' => [false, null],
            'shipping_cost' => [false, 0],
            'discount_amount' => [false, 0],
            'total_amount' => [false, null],
        ],
        'order_items' => [
            'price' => [false, null],
            'total' => [false, null],
        ],
        'coupons' => [
            'value' => [false, null],
            'min_order_amount' => [true, null],
        ],
    ];

    public function up(): void
    {
        $this->guardFractionalPercentCoupons();

        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column => [$nullable, $default]) {
                $legacy = "{$column}_legacy";

                Schema::table($table, fn (Blueprint $t) => $t->renameColumn($column, $legacy));
                Schema::table($table, fn (Blueprint $t) => $t->decimal($legacy, 10, 2)->nullable()->change());
                Schema::table($table, fn (Blueprint $t) => $t->bigInteger($column)->nullable()->after($legacy));

                DB::table($table)->update([$column => DB::raw($this->toPiastersSql($table, $column))]);

                if (!$nullable) {
                    Schema::table($table, function (Blueprint $t) use ($column, $default) {
                        $definition = $t->bigInteger($column)->nullable(false);
                        if ($default !== null) {
                            $definition->default($default * 100);
                        }
                        $definition->change();
                    });
                }

                $this->verify($table, $column);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::COLUMNS, true) as $table => $columns) {
            foreach (array_reverse($columns, true) as $column => [$nullable, $default]) {
                $legacy = "{$column}_legacy";

                // Refresh the legacy value from the current one so rows written after `up` survive the rollback.
                DB::table($table)->update([$legacy => DB::raw($this->toPoundsSql($table, $column))]);

                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
                Schema::table($table, fn (Blueprint $t) => $t->renameColumn($legacy, $column));
                Schema::table($table, function (Blueprint $t) use ($column, $nullable, $default) {
                    $definition = $t->decimal($column, 10, 2)->nullable($nullable);
                    if ($default !== null) {
                        $definition->default($default);
                    }
                    $definition->change();
                });
            }
        }
    }

    private function toPiastersSql(string $table, string $column): string
    {
        $legacy = $this->wrap("{$column}_legacy");

        // Percent coupons keep their whole-number percentage.
        if ($table === 'coupons' && $column === 'value') {
            return "CASE WHEN {$this->wrap('type')} = 'fixed' THEN ROUND({$legacy} * 100) ELSE ROUND({$legacy}) END";
        }

        return "ROUND({$legacy} * 100)";
    }

    private function toPoundsSql(string $table, string $column): string
    {
        $current = $this->wrap($column);

        if ($table === 'coupons' && $column === 'value') {
            return "CASE WHEN {$this->wrap('type')} = 'fixed' THEN {$current} / 100.0 ELSE {$current} END";
        }

        return "{$current} / 100.0";
    }

    /**
     * Abort (the exception stops the migration) if any row did not convert exactly.
     */
    private function verify(string $table, string $column): void
    {
        $legacy = "{$column}_legacy";
        $expected = $this->toPiastersSql($table, $column);

        $mismatches = DB::table($table)
            ->where(function ($q) use ($column, $legacy, $expected) {
                $q->whereRaw("({$this->wrap($column)} IS NULL) <> ({$this->wrap($legacy)} IS NULL)")
                    ->orWhereRaw("ABS({$this->wrap($column)} - ({$expected})) > 0");
            })
            ->count();

        if ($mismatches > 0) {
            throw new RuntimeException("Money conversion mismatch in {$table}.{$column}: {$mismatches} row(s).");
        }
    }

    private function guardFractionalPercentCoupons(): void
    {
        $fractional = DB::table('coupons')
            ->where('type', '!=', 'fixed')
            ->whereRaw("{$this->wrap('value')} <> ROUND({$this->wrap('value')})")
            ->pluck('code');

        if ($fractional->isNotEmpty()) {
            throw new RuntimeException(
                'Percent coupons with fractional values cannot be converted to integers: ' . $fractional->implode(', ')
            );
        }
    }

    private function wrap(string $column): string
    {
        return DB::connection()->getQueryGrammar()->wrap($column);
    }
};
