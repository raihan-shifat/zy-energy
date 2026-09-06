<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->date('visit_date')->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            // One row per visitor session per day = one "visit"
            $table->unique(['session_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
