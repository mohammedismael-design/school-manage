<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('school_role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('school_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('staff_role_assignments', function (Blueprint $table) {
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('school_roles')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'role_id']);
        });

        Schema::create('staff_direct_permissions', function (Blueprint $table) {
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'permission_id']);
        });

        Schema::create('role_hierarchy', function (Blueprint $table) {
            $table->foreignId('parent_role_id')->constrained('school_roles')->cascadeOnDelete();
            $table->foreignId('child_role_id')->constrained('school_roles')->cascadeOnDelete();

            $table->unique(['parent_role_id', 'child_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_hierarchy');
        Schema::dropIfExists('staff_direct_permissions');
        Schema::dropIfExists('staff_role_assignments');
        Schema::dropIfExists('school_role_permissions');
        Schema::dropIfExists('school_roles');
    }
};
