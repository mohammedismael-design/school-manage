<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'icon',
        'description',
        'category',
        'category_icon',
        'dependencies',
        'permissions',
        'settings',
        'sort_order',
        'is_core',
        'is_active',
    ];

    protected $casts = [
        'dependencies' => 'array',
        'permissions' => 'array',
        'settings' => 'array',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'module_tenant')
            ->withPivot(['is_enabled', 'settings', 'permissions_override'])
            ->withTimestamps();
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'plan_modules', 'module_id', 'plan_id')
            ->withPivot(['is_included'])
            ->withTimestamps();
    }
}
