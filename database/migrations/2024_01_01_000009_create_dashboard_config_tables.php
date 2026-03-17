<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('user_type');
            $table->string('config_key');
            $table->json('config_value')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_type', 'config_key']);
        });

        Schema::create('user_dashboard_overrides', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('config_key');
            $table->json('config_value')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'config_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_overrides');
        Schema::dropIfExists('dashboard_configs');
    }
};
