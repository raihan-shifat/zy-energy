<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            // Enquiry form selects a Category instead of a specific Product
            $table->foreignId('category_id')->nullable()->after('product_id')
                ->constrained('categories')->nullOnDelete();
            $table->string('category_name')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('category_name');
        });
    }
};
