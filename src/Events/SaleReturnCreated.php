<?php

namespace Gopos\Events;

use Gopos\Models\SaleReturn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleReturnCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SaleReturn $saleReturn
    ) {}
}
