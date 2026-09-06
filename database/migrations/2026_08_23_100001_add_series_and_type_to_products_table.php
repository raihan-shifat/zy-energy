<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add B2B catalogue fields (Series + sub-type) to products.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('series')->nullable()->after('product_type');
            $table->string('type')->nullable()->after('series');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['series', 'type']);
        });
    }
};
