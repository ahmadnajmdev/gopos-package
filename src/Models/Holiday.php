<?php

namespace Gopos\Models;

use Gopos\Enums\HolidayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'date',
        'type',
        'is_recurring',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'type' => HolidayType::class,
            'is_recurring' => 'boolean',
        ];
    }
}
