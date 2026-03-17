<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_enabled')->default(false);
            $table->text('credentials')->nullable()->comment('Encrypted credentials');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'code']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('mpesa');
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('pending')
                ->comment('pending|completed|failed|refunded');
            $table->timestamp('paid_at')->nullable();
            $table->string('invoice_number')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('payment_methods');
    }
};
