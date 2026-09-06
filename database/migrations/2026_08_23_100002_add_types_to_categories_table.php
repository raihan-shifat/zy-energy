<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add per-category configurable sub-type options (e.g. Horizontal/Vertical).
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->json('types')->nullable()->after('parent_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('types');
        });
    }
};
