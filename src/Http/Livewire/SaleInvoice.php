<?php

namespace Gopos\Http\Livewire;

use Gopos\Models\Sale;
use Livewire\Component;

class SaleInvoice extends Component
{
    public $sale;

    public function mount(Sale $sale)
    {
        $this->sale = $sale;
    }

    public function render()
    {
        return view('gopos::livewire.sale-invoice');
    }
}
