<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'paid_at',
        'invoice_number',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('INV-%d-%05d', $year, $count);
    }
}
