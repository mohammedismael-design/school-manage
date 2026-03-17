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
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'plan_modules')
            ->withPivot('is_included')
            ->withTimestamps();
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    public function calculatePrice(string $billingCycle = 'monthly'): float
    {
        return $billingCycle === 'yearly'
            ? (float) $this->price_yearly
            : (float) $this->price_monthly;
    }

    public function isUnlimited(string $resource = 'students'): bool
    {
        $limit = match ($resource) {
            'students' => $this->student_limit,
            'staff' => $this->staff_limit,
            default => 0,
        };

        return $limit === 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
