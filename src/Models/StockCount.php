<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCount extends Model
{
    use Auditable;

    protected $fillable = [
        'count_number',
        'warehouse_id',
        'type',
        'status',
        'count_date',
        'created_by',
        'completed_by',
        'started_at',
        'completed_at',
        'adjustments_posted',
        'notes',
    ];

    protected $casts = [
        'count_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'adjustments_posted' => 'boolean',
    ];

    public const TYPE_FULL = 'full';

    public const TYPE_PARTIAL = 'partial';

    public const TYPE_CYCLE = 'cycle';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public static function generateCountNumber(): string
    {
        $lastNumber = static::selectRaw('MAX(CAST(SUBSTRING(count_number, 4) AS UNSIGNED)) as max_num')
            ->value('max_num');
        $nextNumber = ($lastNumber ?? 0) + 1;

        return 'SC-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getTotalVarianceAttribute(): float
    {
        return $this->items()->sum('variance');
    }

    public function getTotalVarianceValueAttribute(): float
    {
        return $this->items()->sum('variance_value');
    }

    public function getItemsCountedAttribute(): int
    {
        return $this->items()->whereNotNull('counted_quantity')->count();
    }

    public function getItemsTotalAttribute(): int
    {
        return $this->items()->count();
    }

    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function complete(User $user): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->count_number)) {
                $model->count_number = static::generateCountNumber();
            }
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
