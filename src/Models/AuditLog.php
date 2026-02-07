<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'tags',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeForModel($query, Model $model)
    {
        return $query->where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeFinancial($query)
    {
        return $query->whereJsonContains('tags', 'financial');
    }

    public function scopeForModelType($query, string $type)
    {
        return $query->where('auditable_type', $type);
    }

    // Accessors

    /**
     * Get the changes between old and new values
     */
    public function getChangesAttribute(): array
    {
        $changes = [];
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];

        // Get all keys from both arrays
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        foreach ($allKeys as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;

            if ($old !== $new) {
                $changes[$key] = [
                    'old' => $old,
                    'new' => $new,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get a readable model name
     */
    public function getModelNameAttribute(): string
    {
        $class = class_basename($this->auditable_type);

        return preg_replace('/([a-z])([A-Z])/', '$1 $2', $class);
    }

    /**
     * Get event label with localization
     */
    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => __('Created'),
            'updated' => __('Updated'),
            'deleted' => __('Deleted'),
            'restored' => __('Restored'),
            'voided' => __('Voided'),
            'posted' => __('Posted'),
            default => ucfirst($this->event),
        };
    }

    /**
     * Get event color for UI
     */
    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            'voided' => 'danger',
            'posted' => 'success',
            default => 'gray',
        };
    }

    // Static logging methods

    /**
     * Log a created event
     */
    public static function logCreated(Model $model, ?array $tags = null): self
    {
        return static::createLog($model, 'created', null, $model->getAttributes(), $tags);
    }

    /**
     * Log an updated event
     */
    public static function logUpdated(Model $model, ?array $tags = null): self
    {
        $oldValues = [];
        $newValues = [];

        foreach ($model->getDirty() as $key => $value) {
            $excludeFields = method_exists($model, 'getAuditExclude') ? $model->getAuditExclude() : [];
            if (in_array($key, $excludeFields)) {
                continue;
            }

            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $value;
        }

        return static::createLog($model, 'updated', $oldValues, $newValues, $tags);
    }

    /**
     * Log a deleted event
     */
    public static function logDeleted(Model $model, ?array $tags = null): self
    {
        return static::createLog($model, 'deleted', $model->getAttributes(), null, $tags);
    }

    /**
     * Log a restored event
     */
    public static function logRestored(Model $model, ?array $tags = null): self
    {
        return static::createLog($model, 'restored', null, $model->getAttributes(), $tags);
    }

    /**
     * Log a voided event
     */
    public static function logVoided(Model $model, string $reason, ?array $tags = null): self
    {
        $tags = array_merge($tags ?? [], ['voided']);

        return static::createLog($model, 'voided', ['reason' => $reason], null, $tags);
    }

    /**
     * Log a posted event
     */
    public static function logPosted(Model $model, ?array $tags = null): self
    {
        $tags = array_merge($tags ?? [], ['posted']);

        return static::createLog($model, 'posted', null, ['status' => 'posted'], $tags);
    }

    /**
     * Create the actual log entry
     */
    protected static function createLog(
        Model $model,
        string $event,
        ?array $oldValues,
        ?array $newValues,
        ?array $tags
    ): self {
        $user = auth()->user();

        // Filter out excluded fields
        $excludeFields = method_exists($model, 'getAuditExclude')
            ? $model->getAuditExclude()
            : ['password', 'remember_token', 'created_at', 'updated_at'];

        if ($oldValues) {
            $oldValues = array_diff_key($oldValues, array_flip($excludeFields));
        }

        if ($newValues) {
            $newValues = array_diff_key($newValues, array_flip($excludeFields));
        }

        // Determine tags based on model type
        $defaultTags = static::getDefaultTags($model);
        $tags = array_unique(array_merge($defaultTags, $tags ?? []));

        return static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'tags' => $tags,
        ]);
    }

    /**
     * Get default tags based on model type
     */
    protected static function getDefaultTags(Model $model): array
    {
        $financialModels = [
            Sale::class,
            SaleItem::class,
            SaleReturn::class,
            Purchase::class,
            PurchaseItem::class,
            PurchaseReturn::class,
            Payment::class,
            Expense::class,
            Income::class,
            JournalEntry::class,
            JournalEntryLine::class,
            Account::class,
            TaxCode::class,
        ];

        if (in_array(get_class($model), $financialModels)) {
            return ['financial'];
        }

        return [];
    }
}
