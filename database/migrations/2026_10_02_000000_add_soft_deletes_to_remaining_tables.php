<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nothing the admin or a customer deletes is removed for real any more: every table that did not have
 * soft deletes yet gets deleted_at (users, categories, products and offers already had it).
 * Carts, wishlists and comparisons are left out on purpose: they are a customer's current picks, not records.
 *
 * coupons.code and users.email stop being unique in the database, so a deleted coupon's code or a deleted
 * account's email can be used again. Uniqueness among the rows that are not deleted is checked by validation
 * (MySQL has no unique index that skips deleted rows).
 */
return new class extends Migration
{
    private const TABLES = [
        'coupons', 'banners', 'posts', 'partners', 'certificates', 'team_members',
        'addresses', 'product_features', 'product_images', 'orders', 'order_items',
    ];

    // table => column that was unique and becomes a plain index
    private const REUSABLE = ['coupons' => 'code', 'users' => 'email'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
        }

        foreach (self::REUSABLE as $table => $column) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropUnique([$column]);
                $t->index($column);
            });
        }
    }

    public function down(): void
    {
        // Without deleted_at, deleted rows would show up again, and a reused code or email breaks the unique index.
        $problems = [];

        foreach (self::TABLES as $table) {
            $deleted = DB::table($table)->whereNotNull('deleted_at')->count();
            if ($deleted > 0) {
                $problems[] = "{$table}: {$deleted} deleted row(s) would become visible again";
            }
        }

        foreach (self::REUSABLE as $table => $column) {
            $duplicates = DB::table($table)->select($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->pluck($column);
            if ($duplicates->isNotEmpty()) {
                $problems[] = "{$table}.{$column}: used more than once (" . $duplicates->take(5)->implode(', ') . ')';
            }
        }

        if ($problems) {
            throw new RuntimeException(
                "Cannot roll back soft deletes without changing data. Restore or remove these rows first:\n- " . implode("\n- ", $problems)
            );
        }

        foreach (self::REUSABLE as $table => $column) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropIndex([$column]);
                $t->unique($column);
            });
        }

        foreach (self::TABLES as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
        }
    }
};
