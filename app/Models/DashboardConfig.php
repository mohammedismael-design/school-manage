<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_type',
        'config_key',
        'config_value',
        'created_by',
    ];

    protected $casts = [
        'config_value' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForUserType($query, string $userType)
    {
        return $query->where('user_type', $userType);
    }
}
