<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Multi-language (up to 2 separate PDFs) and multi-currency display (max 2)
            $table->json('languages')->nullable()->after('language');
            $table->json('currencies')->nullable()->after('display_currency');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            // Manually uploaded reference image for non-catalog line items
            $table->string('image_path')->nullable()->after('reference_image');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['languages', 'currencies']);
        });
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
