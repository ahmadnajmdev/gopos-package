<?php

namespace Gopos\Models;

use Gopos\Enums\InstallmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'status' => InstallmentStatus::class,
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function getRemainingAttribute(): float
    {
        return (float) $this->amount - (float) $this->paid_amount;
    }

    public function isPaid(): bool
    {
        return (float) $this->paid_amount >= (float) $this->amount;
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && ! $this->isPaid();
    }
}
