<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;

class SubscriptionService
{
    /**
     * Check whether the tenant has reached their student limit.
     */
    public function hasReachedStudentLimit(Tenant $tenant): bool
    {
        $plan = $tenant->plan;

        if (! $plan || $plan->isUnlimited('students')) {
            return false;
        }

        $currentCount = $tenant->users()
            ->where('user_type', 'student')
            ->count();

        return $currentCount >= $plan->student_limit;
    }

    /**
     * Check whether the tenant has reached their staff limit.
     */
    public function hasReachedStaffLimit(Tenant $tenant): bool
    {
        $plan = $tenant->plan;

        if (! $plan || $plan->isUnlimited('staff')) {
            return false;
        }

        $staffTypes = ['admin', 'principal', 'teacher', 'staff', 'driver'];
        $currentCount = $tenant->users()
            ->whereIn('user_type', $staffTypes)
            ->count();

        return $currentCount >= $plan->staff_limit;
    }

    /**
     * Calculate the price for a plan and billing cycle, optionally including add-ons.
     */
    public function calculateTotal(SubscriptionPlan $plan, string $cycle = 'monthly', array $addons = []): float
    {
        $base = $plan->calculatePrice($cycle);

        $addonTotal = array_sum(array_column($addons, $cycle === 'monthly' ? 'monthly' : 'yearly'));

        return $base + $addonTotal;
    }
}
