<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Email is now optional on the enquiry form (only Name + Message are required)
        DB::statement('ALTER TABLE enquiries MODIFY email VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE enquiries MODIFY email VARCHAR(255) NOT NULL DEFAULT ''");
    }
};
