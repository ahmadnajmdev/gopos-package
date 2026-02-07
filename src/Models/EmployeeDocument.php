<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'document_type',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'expiry_date',
        'is_verified',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public const TYPE_CONTRACT = 'contract';

    public const TYPE_ID_CARD = 'id_card';

    public const TYPE_PASSPORT = 'passport';

    public const TYPE_CERTIFICATE = 'certificate';

    public const TYPE_RESUME = 'resume';

    public const TYPE_PHOTO = 'photo';

    public const TYPE_OTHER = 'other';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            self::TYPE_CONTRACT => __('Employment Contract'),
            self::TYPE_ID_CARD => __('ID Card'),
            self::TYPE_PASSPORT => __('Passport'),
            self::TYPE_CERTIFICATE => __('Certificate'),
            self::TYPE_RESUME => __('Resume/CV'),
            self::TYPE_PHOTO => __('Photo'),
            self::TYPE_OTHER => __('Other'),
            default => $this->document_type,
        };
    }

    /**
     * Get file size formatted.
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    /**
     * Check if expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check if expiring soon.
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Verify document.
     */
    public function verify(int $userId): void
    {
        $this->update([
            'is_verified' => true,
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }
}
