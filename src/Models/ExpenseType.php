<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
