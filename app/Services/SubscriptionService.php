<?php

namespace App\Services;

use App\Models\Module;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Support\Collection;

class SubscriptionService
{
    public function getAvailablePlans(): Collection
    {
        return SubscriptionPlan::active()
            ->with(['modules' => fn ($q) => $q->where('plan_modules.is_included', true)])
            ->get();
    }

    public function getPlanModules(int $planId): Collection
    {
        return Module::whereHas('plans', fn ($q) =>
            $q->where('plan_modules.plan_id', $planId)
              ->where('plan_modules.is_included', true)
        )->active()->get();
    }

    public function getAddonModules(): Collection
    {
        return Module::active()
            ->whereNotIn('key', [
                'core', 'academics', 'attendance', 'communication',
                'finance', 'parent_portal',
            ])
            ->get();
    }

    public function calculatePrice(int $planId, array $addons, string $billingCycle): array
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        $basePrice = $plan->calculatePrice($billingCycle);

        $addonPrices = [
            'transport' => ['monthly' => 5000, 'yearly' => 50000],
            'store' => ['monthly' => 3000, 'yearly' => 30000],
            'hostel' => ['monthly' => 4000, 'yearly' => 40000],
            'alumni' => ['monthly' => 2000, 'yearly' => 20000],
        ];

        $addonTotal = 0;
        foreach ($addons as $addon) {
            $addonTotal += $addonPrices[$addon][$billingCycle] ?? 0;
        }

        $total = $basePrice + $addonTotal;

        return [
            'base_price' => $basePrice,
            'addon_total' => $addonTotal,
            'total' => $total,
            'billing_cycle' => $billingCycle,
            'savings' => $billingCycle === 'yearly'
                ? (($plan->price_monthly * 12) + ($this->calculateAddonMonthlyTotal($addons, $addonPrices) * 12)) - $total
                : 0,
        ];
    }

    public function checkSubscriptionLimits(Tenant $tenant, string $resourceType): array
    {
        $usage = $this->getUsageStats($tenant);
        $limit = match ($resourceType) {
            'students' => $tenant->max_students,
            'staff' => $tenant->max_staff,
            'storage' => $tenant->max_storage_mb,
            default => 0,
        };

        $current = $usage[$resourceType] ?? 0;
        $isUnlimited = $limit === 0;

        return [
            'can_add' => $isUnlimited || $current < $limit,
            'current' => $current,
            'limit' => $limit,
            'is_unlimited' => $isUnlimited,
            'remaining' => $isUnlimited ? null : max(0, $limit - $current),
            'percentage' => $isUnlimited ? 0 : ($limit > 0 ? round(($current / $limit) * 100, 1) : 0),
            'is_near_limit' => !$isUnlimited && $limit > 0 && ($current / $limit) >= 0.8,
            'is_at_limit' => !$isUnlimited && $current >= $limit,
        ];
    }

    public function getRemainingSlots(Tenant $tenant, string $resourceType): ?int
    {
        $limits = $this->checkSubscriptionLimits($tenant, $resourceType);

        return $limits['remaining'];
    }

    public function isModuleAccessible(Tenant $tenant, string $moduleKey): bool
    {
        if (!$tenant->isActive() && !$tenant->isOnTrial()) {
            return false;
        }

        return $tenant->isModuleEnabled($moduleKey);
    }

    public function upgradeSubscription(Tenant $tenant, int $newPlanId, string $billingCycle): Tenant
    {
        $newPlan = SubscriptionPlan::findOrFail($newPlanId);

        $oldPlanId = $tenant->plan_id;
        $tenant->plan_id = $newPlanId;
        $tenant->billing_cycle = $billingCycle;
        $tenant->max_students = $newPlan->student_limit ?: 999999;
        $tenant->max_staff = $newPlan->staff_limit ?: 999999;
        $tenant->max_storage_mb = $newPlan->storage_limit_mb;
        $tenant->subscription_start_date = now();
        $tenant->subscription_end_date = $billingCycle === 'yearly'
            ? now()->addYear()
            : now()->addMonth();
        $tenant->subscription_status = 'active';
        $tenant->save();

        AuditLog::record(
            'subscription.upgraded',
            $tenant,
            ['plan_id' => $oldPlanId],
            ['plan_id' => $newPlanId, 'billing_cycle' => $billingCycle]
        );

        return $tenant;
    }

    public function downgradeSubscription(Tenant $tenant, int $newPlanId): Tenant
    {
        return $this->upgradeSubscription($tenant, $newPlanId, $tenant->billing_cycle);
    }

    public function getUsageStats(Tenant $tenant): array
    {
        $students = $tenant->users()->where('user_type', 'student')->count();
        $staff = $tenant->users()
            ->whereNotIn('user_type', ['student', 'parent'])
            ->count();

        // Calculate storage usage (placeholder - implement based on actual file storage)
        $storageMb = 0;

        return [
            'students' => $students,
            'staff' => $staff,
            'storage' => $storageMb,
        ];
    }

    private function calculateAddonMonthlyTotal(array $addons, array $addonPrices): float
    {
        $total = 0;
        foreach ($addons as $addon) {
            $total += $addonPrices[$addon]['monthly'] ?? 0;
        }

        return $total;
    }
}
