<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->json('colors')->nullable();
            $table->string('domain')->nullable()->unique();
            $table->string('subdomain')->nullable()->unique();
            $table->string('status')->default('trial')
                ->comment('active|suspended|trial|inactive');
            $table->string('subscription_status')->default('trial')
                ->comment('active|past_due|canceled|expired|trial');
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->timestamp('subscription_start_date')->nullable();
            $table->timestamp('subscription_end_date')->nullable();
            $table->string('billing_cycle')->default('monthly')
                ->comment('monthly|yearly');
            $table->unsignedInteger('max_students')->default(200);
            $table->unsignedInteger('max_staff')->default(20);
            $table->unsignedInteger('max_storage_mb')->default(2048);
            $table->json('addon_modules')->nullable();
            $table->json('settings')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
