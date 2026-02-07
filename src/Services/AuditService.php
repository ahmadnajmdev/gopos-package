<?php

namespace Gopos\Services;

use Gopos\Models\AuditLog;
use Gopos\Models\JournalEntry;
use Gopos\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AuditService
{
    /**
     * Log a custom event
     */
    public function log(
        Model $model,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $tags = null
    ): AuditLog {
        $user = auth()->user();

        return AuditLog::create([
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
     * Get audit history for a model
     */
    public function getAuditHistory(Model $model, int $limit = 50): Collection
    {
        return AuditLog::forModel($model)
            ->with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(int $limit = 100): Collection
    {
        return AuditLog::with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity for a specific user
     */
    public function getUserActivity(User $user, ?string $startDate = null, int $limit = 100): Collection
    {
        $query = AuditLog::byUser($user->id)
            ->with('user')
            ->latest('created_at');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get financial audit trail
     */
    public function getFinancialAuditTrail(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = AuditLog::financial()
            ->with('user')
            ->latest('created_at');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->get();
    }

    /**
     * Log a void event
     */
    public function logVoid(Model $model, string $reason, ?array $tags = null): AuditLog
    {
        $tags = array_merge($tags ?? [], ['voided', 'financial']);

        return $this->log($model, 'voided', ['void_reason' => $reason], null, $tags);
    }

    /**
     * Log a post event (for journal entries)
     */
    public function logPost(JournalEntry $entry, ?array $tags = null): AuditLog
    {
        $tags = array_merge($tags ?? [], ['posted', 'financial']);

        return $this->log(
            $entry,
            'posted',
            ['status' => 'draft'],
            ['status' => 'posted', 'posted_at' => now()->toDateTimeString()],
            $tags
        );
    }

    /**
     * Log an approval event
     */
    public function logApproval(Model $model, User $approver, ?array $tags = null): AuditLog
    {
        $tags = array_merge($tags ?? [], ['approved']);

        return $this->log(
            $model,
            'approved',
            null,
            ['approved_by' => $approver->id, 'approved_at' => now()->toDateTimeString()],
            $tags
        );
    }

    /**
     * Get audit logs by model type
     */
    public function getLogsByModelType(string $modelType, int $limit = 100): Collection
    {
        return AuditLog::forModelType($modelType)
            ->with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit summary statistics
     */
    public function getAuditSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $query = AuditLog::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $eventCounts = (clone $query)
            ->selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->pluck('count', 'event')
            ->toArray();

        $userCounts = (clone $query)
            ->selectRaw('user_id, user_name, COUNT(*) as count')
            ->groupBy('user_id', 'user_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        $modelCounts = (clone $query)
            ->selectRaw('auditable_type, COUNT(*) as count')
            ->groupBy('auditable_type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                $item['model_name'] = class_basename($item['auditable_type']);

                return $item;
            })
            ->toArray();

        return [
            'total_events' => $query->count(),
            'by_event' => $eventCounts,
            'by_user' => $userCounts,
            'by_model' => $modelCounts,
        ];
    }

    /**
     * Clean up old audit logs (for maintenance)
     */
    public function cleanupOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
    }
}
