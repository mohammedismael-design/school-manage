<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'price_monthly',
        'price_yearly',
        'student_limit',
        'staff_limit',
        'storage_limit_mb',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'float',
        'price_yearly' => 'float',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'plan_modules', 'plan_id', 'module_id')
            ->withPivot(['is_included'])
            ->withTimestamps();
    }

    /**
     * Calculate price for a given billing cycle.
     */
    public function calculatePrice(string $cycle = 'monthly'): float
    {
        return match ($cycle) {
            'yearly', 'annual' => (float) $this->price_yearly,
            default => (float) $this->price_monthly,
        };
    }

    /**
     * Check if the plan has unlimited capacity for a resource type.
     * A limit of 0 means unlimited.
     */
    public function isUnlimited(string $resource): bool
    {
        return match ($resource) {
            'students' => (int) $this->student_limit === 0,
            'staff' => (int) $this->staff_limit === 0,
            'storage' => (int) $this->storage_limit_mb === 0,
            default => false,
        };
    }
}
