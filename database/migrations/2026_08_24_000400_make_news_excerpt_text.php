<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Excerpts can be longer than 255 chars when an admin pastes a summary;
        // widen to TEXT so saving never crashes with "Data too long" (1406).
        DB::statement('ALTER TABLE news_translations MODIFY excerpt TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE news_translations MODIFY excerpt VARCHAR(255) NULL');
    }
};
