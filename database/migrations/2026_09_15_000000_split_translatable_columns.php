<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits every user-facing text column into an Arabic and an English column.
 *
 * Existing content is in mixed languages, so the current value is copied into
 * both columns; admins correct the wrong side from the dashboard afterwards.
 */
return new class extends Migration
{
    /** @var array<string, array<string, string>> table => [field => column type] */
    private array $translatable = [
        'categories' => ['name' => 'string', 'description' => 'text'],
        'products' => ['name' => 'string', 'description' => 'text'],
        'product_features' => ['name' => 'string'],
        'banners' => ['title' => 'string', 'description' => 'text'],
        'posts' => ['title' => 'string', 'description' => 'text'],
    ];

    public function up(): void
    {
        foreach ($this->translatable as $table => $fields) {
            Schema::table($table, function (Blueprint $blueprint) use ($fields) {
                foreach ($fields as $field => $type) {
                    // Both are placed after the original column in one ALTER, so
                    // adding _en first leaves the final order as _ar, _en.
                    $blueprint->{$type}("{$field}_en")->nullable()->after($field);
                    $blueprint->{$type}("{$field}_ar")->nullable()->after($field);
                }
            });

            $copy = [];
            foreach (array_keys($fields) as $field) {
                $column = DB::raw(DB::getQueryGrammar()->wrap($field));
                $copy["{$field}_ar"] = $column;
                $copy["{$field}_en"] = $column;
            }
            // Query builder, not Eloquent, so soft-deleted rows are copied too.
            DB::table($table)->update($copy);

            Schema::table($table, function (Blueprint $blueprint) use ($fields) {
                $blueprint->dropColumn(array_keys($fields));
            });
        }
    }

    public function down(): void
    {
        foreach ($this->translatable as $table => $fields) {
            Schema::table($table, function (Blueprint $blueprint) use ($fields) {
                foreach ($fields as $field => $type) {
                    $blueprint->{$type}($field)->nullable()->after("{$field}_ar");
                }
            });

            $grammar = DB::getQueryGrammar();
            $copy = [];
            foreach (array_keys($fields) as $field) {
                $copy[$field] = DB::raw(sprintf(
                    'COALESCE(%s, %s)',
                    $grammar->wrap("{$field}_ar"),
                    $grammar->wrap("{$field}_en"),
                ));
            }
            DB::table($table)->update($copy);

            Schema::table($table, function (Blueprint $blueprint) use ($fields) {
                foreach (array_keys($fields) as $field) {
                    $blueprint->dropColumn(["{$field}_ar", "{$field}_en"]);
                }
            });
        }
    }
};
