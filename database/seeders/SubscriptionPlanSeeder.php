<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'code' => 'basic',
                'description' => 'Perfect for small schools. Covers core academics and communication.',
                'price_monthly' => 5000,
                'price_yearly' => 50000,
                'student_limit' => 200,
                'staff_limit' => 20,
                'storage_limit_mb' => 2048,
                'features' => ['report_cards', 'attendance_tracking', 'parent_sms'],
                'is_active' => true,
                'sort_order' => 1,
                'module_keys' => ['core', 'academics', 'attendance', 'communication'],
            ],
            [
                'name' => 'Standard',
                'code' => 'standard',
                'description' => 'For growing schools. Includes finance management and parent portal.',
                'price_monthly' => 12000,
                'price_yearly' => 120000,
                'student_limit' => 500,
                'staff_limit' => 50,
                'storage_limit_mb' => 10240,
                'features' => ['report_cards', 'attendance_tracking', 'parent_sms', 'fee_management', 'parent_portal'],
                'is_active' => true,
                'sort_order' => 2,
                'module_keys' => ['core', 'academics', 'attendance', 'communication', 'finance', 'parent_portal'],
            ],
            [
                'name' => 'Premium',
                'code' => 'premium',
                'description' => 'Full-featured for established schools. NEMIS integration included.',
                'price_monthly' => 25000,
                'price_yearly' => 250000,
                'student_limit' => 1000,
                'staff_limit' => 100,
                'storage_limit_mb' => 51200,
                'features' => ['report_cards', 'attendance_tracking', 'parent_sms', 'fee_management', 'parent_portal', 'transport', 'store', 'nemis', 'student_portal'],
                'is_active' => true,
                'sort_order' => 3,
                'module_keys' => ['core', 'academics', 'attendance', 'communication', 'finance', 'parent_portal', 'transport', 'store', 'nemis', 'student_portal'],
            ],
            [
                'name' => 'Enterprise',
                'code' => 'enterprise',
                'description' => 'Unlimited capacity with all modules. Custom integrations available.',
                'price_monthly' => 45000,
                'price_yearly' => 450000,
                'student_limit' => 0,
                'staff_limit' => 0,
                'storage_limit_mb' => 0,
                'features' => ['all_features', 'custom_integrations', 'dedicated_support', 'sla'],
                'is_active' => true,
                'sort_order' => 4,
                'module_keys' => [],
            ],
        ];

        foreach ($plans as $planData) {
            $moduleKeys = $planData['module_keys'];
            unset($planData['module_keys']);

            $plan = SubscriptionPlan::updateOrCreate(
                ['code' => $planData['code']],
                $planData
            );

            if ($moduleKeys) {
                $moduleIds = Module::whereIn('key', $moduleKeys)->pluck('id');
                $plan->modules()->sync(
                    $moduleIds->mapWithKeys(fn ($id) => [$id => ['is_included' => true]])
                );
            } else {
                // Enterprise gets all modules
                $allModuleIds = Module::pluck('id');
                $plan->modules()->sync(
                    $allModuleIds->mapWithKeys(fn ($id) => [$id => ['is_included' => true]])
                );
            }
        }
    }
}
