<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLocation extends Model
{
    protected $fillable = [
        'warehouse_id',
        'name',
        'aisle',
        'shelf',
        'bin',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'location_id');
    }

    public function getFullPathAttribute(): string
    {
        $parts = array_filter([$this->aisle, $this->shelf, $this->bin]);

        return $parts ? implode('-', $parts) : $this->name;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
