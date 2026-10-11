<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Become a Volta distributor" requests from traders, sent from the storefront or added by an admin.
     */
    public function up(): void
    {
        Schema::create('distributor_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->index();
            $table->string('governorate', 50)->index(); // English name, see App\Support\Governorates
            $table->string('center');                   // المركز
            $table->string('area');                     // القرية أو الحي
            $table->boolean('has_commercial_register');
            $table->boolean('has_shop');
            $table->text('notes')->nullable();          // written by the applicant
            $table->string('status', 20)->default('new')->index();
            $table->text('admin_notes')->nullable();    // internal, never shown on the site
            $table->string('source', 20)->default('website');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_requests');
    }
};
