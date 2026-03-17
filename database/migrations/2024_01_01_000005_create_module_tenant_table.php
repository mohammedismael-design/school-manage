<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_tenant', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->json('permissions_override')->nullable();
            $table->string('override_reason')->nullable();
            $table->unsignedBigInteger('overridden_by')->nullable();
            $table->timestamps();

            $table->unique(['module_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_tenant');
    }
};
