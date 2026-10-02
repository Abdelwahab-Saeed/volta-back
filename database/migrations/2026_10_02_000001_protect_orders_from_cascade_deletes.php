<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a product, a category or a customer for real (outside the app, or a future forceDelete) used to
 * cascade into the orders: order lines went with their product, whole orders with their customer.
 * Now the database refuses to drop a product or category that is still referenced, and an order outlives
 * its customer's account (it carries its own copy of the name, phone and address).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->replaceForeign('order_items', 'product_id', 'products', fn ($foreign) => $foreign->restrictOnDelete());
        $this->replaceForeign('products', 'category_id', 'categories', fn ($foreign) => $foreign->restrictOnDelete());
        $this->replaceForeign('orders', 'user_id', 'users', fn ($foreign) => $foreign->nullOnDelete());
    }

    public function down(): void
    {
        foreach ([['order_items', 'product_id', 'products'], ['products', 'category_id', 'categories'], ['orders', 'user_id', 'users']] as [$table, $column, $references]) {
            $this->replaceForeign($table, $column, $references, fn ($foreign) => $foreign->cascadeOnDelete());
        }
    }

    private function replaceForeign(string $table, string $column, string $references, callable $onDelete): void
    {
        Schema::table($table, fn (Blueprint $t) => $t->dropForeign([$column]));
        Schema::table($table, fn (Blueprint $t) => $onDelete($t->foreign($column)->references('id')->on($references)));
    }
};
