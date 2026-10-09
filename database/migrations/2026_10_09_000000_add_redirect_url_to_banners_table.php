<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional link the store opens when a banner is tapped: a full http(s) URL or a store path such as /offers/3.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('redirect_url', 2048)->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('redirect_url');
        });
    }
};
