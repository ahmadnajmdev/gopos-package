<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeType extends Model
{
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
}
