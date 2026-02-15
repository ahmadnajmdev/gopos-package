<?php

namespace Gopos\Console\Commands;

use Gopos\Enums\QuotationStatus;
use Gopos\Models\Quotation;
use Illuminate\Console\Command;

class ExpireQuotations extends Command
{
    protected $signature = 'gopos:expire-quotations';

    protected $description = 'Mark quotations as expired when their valid_until date has passed';

    public function handle(): int
    {
        $count = Quotation::query()
            ->whereIn('status', [QuotationStatus::Draft, QuotationStatus::Sent])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now())
            ->update(['status' => QuotationStatus::Expired->value]);

        $this->info("Expired {$count} quotation(s).");

        return self::SUCCESS;
    }
}
