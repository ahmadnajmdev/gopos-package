<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
