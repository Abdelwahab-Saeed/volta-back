<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // Basic info (translatable)
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('image')->nullable();

            // Type: percentage | fixed | bundle | buy_x_get_y | spend_x_get_y
            $table->enum('type', ['percentage', 'fixed', 'bundle', 'buy_x_get_y', 'spend_x_get_y']);

            // --- percentage & fixed ---
            // value: the percentage (e.g. 20) or fixed amount (e.g. 50)
            $table->decimal('value', 10, 2)->nullable();

            // --- buy_x_get_y ---
            $table->unsignedInteger('buy_quantity')->nullable(); // buy X
            $table->unsignedInteger('get_quantity')->nullable(); // get Y free
            $table->foreignId('get_product_id')->nullable()->constrained('products')->nullOnDelete(); // free product (null = same product)

            // --- spend_x_get_y ---
            $table->decimal('min_spend', 10, 2)->nullable();    // spend X
            $table->decimal('discount_amount', 10, 2)->nullable(); // get Y off

            // --- bundle ---
            $table->decimal('bundle_price', 10, 2)->nullable(); // total price for all bundle products

            // Scheduling & status
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
