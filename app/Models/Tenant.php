<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo',
        'favicon',
        'colors',
        'domain',
        'subdomain',
        'status',
        'subscription_status',
        'plan_id',
        'subscription_start_date',
        'subscription_end_date',
        'billing_cycle',
        'max_students',
        'max_staff',
        'max_storage_mb',
        'addon_modules',
        'settings',
        'features',
    ];

    protected $casts = [
        'colors' => 'array',
        'addon_modules' => 'array',
        'settings' => 'array',
        'features' => 'array',
        'subscription_start_date' => 'datetime',
        'subscription_end_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Tenant $tenant) {
            Cache::forget("tenant.{$tenant->id}");
            Cache::forget("tenant.slug.{$tenant->slug}");
        });

        static::deleted(function (Tenant $tenant) {
            Cache::forget("tenant.{$tenant->id}");
            Cache::forget("tenant.slug.{$tenant->slug}");
        });
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_tenant')
            ->withPivot(['is_enabled', 'settings', 'permissions_override', 'override_reason', 'overridden_by'])
            ->withTimestamps();
    }

    public function enabledModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_enabled', true);
    }

    public function schoolRoles(): HasMany
    {
        return $this->hasMany(SchoolRole::class);
    }

    public function dashboardConfigs(): HasMany
    {
        return $this->hasMany(DashboardConfig::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trial']);
    }

    public function isModuleEnabled(string $moduleKey): bool
    {
        return Cache::remember("tenant.{$this->id}.module.{$moduleKey}", 300, function () use ($moduleKey) {
            return $this->enabledModules()
                ->where('key', $moduleKey)
                ->exists();
        });
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
        $this->save();
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public static function findBySlug(string $slug): ?self
    {
        return Cache::remember("tenant.slug.{$slug}", 600, function () use ($slug) {
            return static::where('slug', $slug)->first();
        });
    }

    public static function findCached(int $id): ?self
    {
        return Cache::remember("tenant.{$id}", 600, function () use ($id) {
            return static::find($id);
        });
    }
}
