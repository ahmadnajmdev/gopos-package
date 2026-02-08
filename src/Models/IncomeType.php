<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class IncomeType extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
    ];

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
}
