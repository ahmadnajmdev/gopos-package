<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'days_allowed',
        'is_paid',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_allowed' => 'integer',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }
}
