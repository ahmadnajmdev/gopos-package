<?php

namespace Gopos\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Gopos\Models\BankReconciliation;
use Gopos\Models\CustomerLoyalty;
use Gopos\Models\HeldSale;
use Gopos\Models\JournalEntryTemplate;
use Gopos\Models\LoyaltyProgram;
use Gopos\Models\PosSession;
use Gopos\Models\ProductBatch;
use Gopos\Models\ProductSerial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApplyBranchScopes
{
    /** @var array<class-string> */
    protected array $models = [
        PosSession::class,
        HeldSale::class,
        ProductBatch::class,
        ProductSerial::class,
        LoyaltyProgram::class,
        CustomerLoyalty::class,
        BankReconciliation::class,
        JournalEntryTemplate::class,
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            foreach ($this->models as $model) {
                $model::addGlobalScope(
                    'branch',
                    fn (Builder $query) => $query->where('branch_id', $tenant->getKey()),
                );

                $model::creating(function ($record) use ($tenant) {
                    if (empty($record->branch_id)) {
                        $record->branch_id = $tenant->getKey();
                    }
                });
            }
        }

        return $next($request);
    }
}
