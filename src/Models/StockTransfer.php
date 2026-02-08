<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use Auditable;
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'transfer_date',
        'expected_date',
        'received_date',
        'created_by',
        'approved_by',
        'received_by',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function generateTransferNumber(): string
    {
        $branchCode = filament()->getTenant()?->code ?? 'MAIN';
        $prefix = $branchCode.'-TR-';
        $prefixLength = strlen($prefix);

        $lastNumber = static::query()
            ->where('transfer_number', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(transfer_number, ?) AS UNSIGNED)) as max_num', [$prefixLength + 1])
            ->value('max_num');

        $nextNumber = ($lastNumber ?? 0) + 1;

        return $prefix.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getTotalValueAttribute(): float
    {
        return $this->items()->sum(\DB::raw('quantity_sent * unit_cost'));
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canReceive(): bool
    {
        return in_array($this->status, [self::STATUS_IN_TRANSIT, self::STATUS_PARTIAL]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInTransit(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_TRANSIT);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->transfer_number)) {
                $model->transfer_number = static::generateTransferNumber();
            }
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
