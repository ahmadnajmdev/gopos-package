<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeQualification extends Model
{
    protected $fillable = [
        'employee_id',
        'qualification_type',
        'title',
        'institution',
        'field_of_study',
        'grade',
        'start_date',
        'end_date',
        'is_completed',
        'certificate_number',
        'certificate_file',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public const TYPE_EDUCATION = 'education';

    public const TYPE_CERTIFICATION = 'certification';

    public const TYPE_TRAINING = 'training';

    public const TYPE_LICENSE = 'license';

    public const TYPE_SKILL = 'skill';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->qualification_type) {
            self::TYPE_EDUCATION => __('Education'),
            self::TYPE_CERTIFICATION => __('Certification'),
            self::TYPE_TRAINING => __('Training'),
            self::TYPE_LICENSE => __('License'),
            self::TYPE_SKILL => __('Skill'),
            default => $this->qualification_type,
        };
    }

    /**
     * Get duration display.
     */
    public function getDurationAttribute(): string
    {
        if (! $this->start_date) {
            return '-';
        }

        $end = $this->end_date ?? ($this->is_completed ? null : now());

        if (! $end) {
            return $this->start_date->format('Y');
        }

        return $this->start_date->format('Y').' - '.$end->format('Y');
    }

    /**
     * Get status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_completed) {
            return __('Completed');
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return __('Incomplete');
        }

        return __('In Progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('qualification_type', $type);
    }

    public function scopeEducation($query)
    {
        return $query->where('qualification_type', self::TYPE_EDUCATION);
    }

    public function scopeCertifications($query)
    {
        return $query->where('qualification_type', self::TYPE_CERTIFICATION);
    }
}
