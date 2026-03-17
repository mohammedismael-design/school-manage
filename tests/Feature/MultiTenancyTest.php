<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_be_created(): void
    {
        $plan = SubscriptionPlan::factory()->create([
            'code' => 'basic',
            'student_limit' => 200,
            'staff_limit' => 20,
        ]);

        $tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'status' => 'active',
        ]);
    }

    public function test_tenant_suspend_persists(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);

        $tenant->suspend();
        $tenant->refresh();

        $this->assertEquals('suspended', $tenant->status);
        $this->assertTrue($tenant->isSuspended());
    }

    public function test_tenant_activate_persists(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'suspended']);

        $tenant->activate();
        $tenant->refresh();

        $this->assertEquals('active', $tenant->status);
        $this->assertTrue($tenant->isActive());
    }

    public function test_users_are_isolated_by_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        User::factory()->create(['tenant_id' => $tenant1->id]);
        User::factory()->create(['tenant_id' => $tenant1->id]);
        User::factory()->create(['tenant_id' => $tenant2->id]);

        $tenant1Users = User::forTenant($tenant1->id)->count();
        $tenant2Users = User::forTenant($tenant2->id)->count();

        $this->assertEquals(2, $tenant1Users);
        $this->assertEquals(1, $tenant2Users);
    }

    public function test_super_admin_has_no_tenant(): void
    {
        $superAdmin = User::factory()->create([
            'user_type' => 'super_admin',
            'tenant_id' => null,
        ]);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertNull($superAdmin->tenant_id);
    }

    public function test_subscription_plan_has_correct_limits(): void
    {
        $plan = SubscriptionPlan::factory()->create([
            'student_limit' => 200,
            'staff_limit' => 20,
            'storage_limit_mb' => 2048,
        ]);

        $this->assertEquals(200, $plan->student_limit);
        $this->assertEquals(20, $plan->staff_limit);
        $this->assertEquals(2048, $plan->storage_limit_mb);
        $this->assertFalse($plan->isUnlimited('students'));
    }

    public function test_enterprise_plan_is_unlimited(): void
    {
        $plan = SubscriptionPlan::factory()->create([
            'student_limit' => 0,
            'staff_limit' => 0,
        ]);

        $this->assertTrue($plan->isUnlimited('students'));
        $this->assertTrue($plan->isUnlimited('staff'));
    }

    public function test_module_can_be_enabled_for_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $module = Module::factory()->create(['key' => 'finance', 'is_active' => true]);

        $tenant->modules()->attach($module->id, ['is_enabled' => true]);

        $this->assertTrue($tenant->enabledModules()->where('key', 'finance')->exists());
    }
}
