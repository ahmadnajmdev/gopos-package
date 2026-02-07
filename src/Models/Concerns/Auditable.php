<?php

namespace Gopos\Models\Concerns;

use Gopos\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    /**
     * Boot the Auditable trait
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            if ($model->shouldAudit('created')) {
                AuditLog::logCreated($model, $model->getAuditTags());
            }
        });

        static::updated(function ($model) {
            if ($model->shouldAudit('updated') && $model->isDirty()) {
                AuditLog::logUpdated($model, $model->getAuditTags());
            }
        });

        static::deleted(function ($model) {
            if ($model->shouldAudit('deleted')) {
                AuditLog::logDeleted($model, $model->getAuditTags());
            }
        });

        // Handle soft deletes if the model uses SoftDeletes
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                if ($model->shouldAudit('restored')) {
                    AuditLog::logRestored($model, $model->getAuditTags());
                }
            });
        }
    }

    /**
     * Get all audit logs for this model
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Get the latest audit log
     */
    public function latestAuditLog()
    {
        return $this->auditLogs()->latest('created_at')->first();
    }

    /**
     * Get audit logs for a specific event
     */
    public function auditLogsForEvent(string $event)
    {
        return $this->auditLogs()->where('event', $event)->get();
    }

    /**
     * Get fields to exclude from audit
     * Override in model to customize
     */
    public function getAuditExclude(): array
    {
        return array_merge(
            ['password', 'remember_token', 'created_at', 'updated_at'],
            $this->auditExclude ?? []
        );
    }

    /**
     * Get fields to include in audit (if set, only these fields are audited)
     * Override in model to customize
     */
    public function getAuditInclude(): array
    {
        return $this->auditInclude ?? [];
    }

    /**
     * Get custom tags for this model's audit entries
     * Override in model to customize
     */
    public function getAuditTags(): array
    {
        return $this->auditTags ?? [];
    }

    /**
     * Determine if a specific event should be audited
     * Override in model to customize
     */
    public function shouldAudit(string $event): bool
    {
        // Check if auditing is disabled globally on the model
        if (property_exists($this, 'disableAuditing') && $this->disableAuditing) {
            return false;
        }

        // Check if specific events are disabled
        $disabledEvents = $this->auditDisabledEvents ?? [];

        return ! in_array($event, $disabledEvents);
    }

    /**
     * Log a custom audit event
     */
    public function logAuditEvent(string $event, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        $user = auth()->user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'tags' => $this->getAuditTags(),
        ]);
    }

    /**
     * Disable auditing temporarily
     */
    public function disableAuditing(): self
    {
        $this->disableAuditing = true;

        return $this;
    }

    /**
     * Enable auditing
     */
    public function enableAuditing(): self
    {
        $this->disableAuditing = false;

        return $this;
    }

    /**
     * Run a callback without auditing
     */
    public static function withoutAuditing(callable $callback)
    {
        $model = new static;
        $model->disableAuditing = true;

        try {
            return $callback($model);
        } finally {
            $model->disableAuditing = false;
        }
    }
}
