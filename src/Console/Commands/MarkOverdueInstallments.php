<?php

namespace Gopos\Console\Commands;

use Gopos\Enums\InstallmentStatus;
use Gopos\Models\SaleInstallment;
use Illuminate\Console\Command;

class MarkOverdueInstallments extends Command
{
    protected $signature = 'gopos:mark-overdue-installments';

    protected $description = 'Mark pending installments as overdue when their due date has passed';

    public function handle(): int
    {
        $count = SaleInstallment::query()
            ->whereIn('status', [InstallmentStatus::Pending, InstallmentStatus::Partial])
            ->where('due_date', '<', now())
            ->update(['status' => InstallmentStatus::Overdue->value]);

        $this->info("Marked {$count} installment(s) as overdue.");

        return self::SUCCESS;
    }
}
