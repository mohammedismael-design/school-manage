<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->default('core')
                ->comment('core|academics|finance|operations|communication|student_services|support');
            $table->string('category_icon')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('permissions')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
