<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_emails', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Display name/label for the email contact');
            $table->string('email')->comment('Email address');
            $table->text('description')->nullable()->comment('Short description');
            $table->string('purpose')->nullable()->comment('Purpose/Department (e.g., Sales, General Information)');
            $table->unsignedInteger('sort_order')->default(0)->comment('Display order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_emails');
    }
};