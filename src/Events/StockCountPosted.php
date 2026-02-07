<?php

namespace Gopos\Events;

use Gopos\Models\StockCount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockCountPosted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public StockCount $stockCount
    ) {}
}
