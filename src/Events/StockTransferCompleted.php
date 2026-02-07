<?php

namespace Gopos\Events;

use Gopos\Models\StockTransfer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockTransferCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public StockTransfer $stockTransfer
    ) {}
}
