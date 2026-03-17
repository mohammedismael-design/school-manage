<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            \Spatie\Permission\Models\Permission::class,
            'school_role_permissions',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffRoleAssignment::class, 'role_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'role_hierarchy',
            'parent_role_id',
            'child_role_id'
        );
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'role_hierarchy',
            'child_role_id',
            'parent_role_id'
        );
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
