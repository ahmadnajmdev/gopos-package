<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans\EmployeeLoanResource;

class CreateEmployeeLoan extends CreateRecord
{
    protected static string $resource = EmployeeLoanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'pending';
        $data['paid_installments'] = 0;
        $data['remaining_amount'] = $data['loan_amount'];

        // Calculate end date
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $data['end_date'] = $startDate->copy()->addMonths($data['installments'] - 1);

        return $data;
    }
}
