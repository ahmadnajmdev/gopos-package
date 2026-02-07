<?php

namespace Gopos\Http\Livewire;

use Gopos\Models\Purchase;
use Livewire\Component;

class InvoicePDF extends Component
{
    public $purchase;

    public function mount(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function render()
    {
        return view('gopos::livewire.invoice-p-d-f');
    }
}
