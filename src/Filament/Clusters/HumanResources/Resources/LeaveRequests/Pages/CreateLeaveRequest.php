<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\LeaveRequestResource;
use Gopos\Models\LeaveRequest;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_at'] = now();
        $data['status'] = 'pending';

        // Calculate days
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate = \Carbon\Carbon::parse($data['end_date']);
        $data['days'] = LeaveRequest::calculateDays($startDate, $endDate, $data['is_half_day'] ?? false);

        return $data;
    }
}
