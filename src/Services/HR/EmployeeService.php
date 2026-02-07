<?php

namespace Gopos\Services\HR;

use Gopos\Models\Employee;
use Gopos\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Create employee.
     */
    public function createEmployee(array $data, ?array $userData = null): Employee
    {
        return DB::transaction(function () use ($data, $userData) {
            // Create linked user if userData provided
            if ($userData) {
                $user = User::create([
                    'name' => $userData['name'] ?? ($data['first_name'].' '.$data['last_name']),
                    'email' => $userData['email'] ?? $data['email'],
                    'password' => Hash::make($userData['password'] ?? 'password'),
                ]);
                $data['user_id'] = $user->id;
            }

            // Create employee
            $employee = Employee::create($data);

            // Initialize leave balances
            $this->leaveService->initializeEmployeeBalances($employee);

            return $employee;
        });
    }

    /**
     * Update employee.
     */
    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        // Update linked user if exists
        if ($employee->user && isset($data['email'])) {
            $employee->user->update(['email' => $data['email']]);
        }

        return $employee->fresh();
    }

    /**
     * Transfer employee to new department/position.
     */
    public function transferEmployee(
        Employee $employee,
        ?int $departmentId = null,
        ?int $positionId = null,
        ?int $managerId = null,
        ?float $newSalary = null
    ): Employee {
        $updates = [];

        if ($departmentId !== null) {
            $updates['department_id'] = $departmentId;
        }

        if ($positionId !== null) {
            $updates['position_id'] = $positionId;
        }

        if ($managerId !== null) {
            $updates['manager_id'] = $managerId;
        }

        if ($newSalary !== null) {
            $updates['basic_salary'] = $newSalary;
        }

        if (! empty($updates)) {
            $employee->update($updates);
        }

        return $employee->fresh();
    }

    /**
     * Terminate employee.
     */
    public function terminateEmployee(Employee $employee, string $reason, ?string $date = null): void
    {
        DB::transaction(function () use ($employee, $reason, $date) {
            $employee->terminate($reason, $date);

            // Deactivate linked user
            if ($employee->user) {
                $employee->user->update(['is_active' => false]);
            }
        });
    }

    /**
     * Reinstate employee.
     */
    public function reinstateEmployee(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            $employee->update([
                'status' => Employee::STATUS_ACTIVE,
                'termination_date' => null,
                'termination_reason' => null,
            ]);

            // Reactivate linked user
            if ($employee->user) {
                $employee->user->update(['is_active' => true]);
            }

            // Initialize leave balances for current year
            $this->leaveService->initializeEmployeeBalances($employee);
        });
    }

    /**
     * Get employees by department.
     */
    public function getByDepartment(int $departmentId, bool $includeSubDepartments = false): Collection
    {
        if ($includeSubDepartments) {
            // Include employees from sub-departments
            $department = \Gopos\Models\Department::find($departmentId);
            $departmentIds = collect([$departmentId])
                ->merge($department->getAllDescendants()->pluck('id'));

            return Employee::whereIn('department_id', $departmentIds)->get();
        }

        return Employee::where('department_id', $departmentId)->get();
    }

    /**
     * Get employee statistics.
     */
    public function getStatistics(): array
    {
        $employees = Employee::all();

        return [
            'total' => $employees->count(),
            'active' => $employees->where('status', Employee::STATUS_ACTIVE)->count(),
            'on_leave' => $employees->where('status', Employee::STATUS_ON_LEAVE)->count(),
            'suspended' => $employees->where('status', Employee::STATUS_SUSPENDED)->count(),
            'terminated' => $employees->where('status', Employee::STATUS_TERMINATED)->count(),
            'resigned' => $employees->where('status', Employee::STATUS_RESIGNED)->count(),
            'by_employment_type' => [
                'full_time' => $employees->where('employment_type', Employee::TYPE_FULL_TIME)->count(),
                'part_time' => $employees->where('employment_type', Employee::TYPE_PART_TIME)->count(),
                'contract' => $employees->where('employment_type', Employee::TYPE_CONTRACT)->count(),
                'temporary' => $employees->where('employment_type', Employee::TYPE_TEMPORARY)->count(),
                'intern' => $employees->where('employment_type', Employee::TYPE_INTERN)->count(),
            ],
            'on_probation' => Employee::onProbation()->count(),
            'contract_expiring' => Employee::contractExpiring()->count(),
        ];
    }

    /**
     * Get birthday employees for month.
     */
    public function getBirthdayEmployees(int $month): Collection
    {
        return Employee::active()
            ->whereMonth('birth_date', $month)
            ->orderByRaw('DAY(birth_date)')
            ->get();
    }

    /**
     * Get work anniversary employees for month.
     */
    public function getWorkAnniversaryEmployees(int $month): Collection
    {
        return Employee::active()
            ->whereMonth('hire_date', $month)
            ->orderByRaw('DAY(hire_date)')
            ->get();
    }

    /**
     * Get employees with expiring contracts.
     */
    public function getExpiringContracts(int $days = 30): Collection
    {
        return Employee::contractExpiring($days)
            ->with('department', 'position')
            ->orderBy('contract_end_date')
            ->get();
    }

    /**
     * Get employees on probation.
     */
    public function getOnProbation(): Collection
    {
        return Employee::onProbation()
            ->with('department', 'position')
            ->orderBy('probation_end_date')
            ->get();
    }

    /**
     * Search employees.
     */
    public function search(string $query): Collection
    {
        return Employee::where(function ($q) use ($query) {
            $q->where('employee_number', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('first_name_ar', 'like', "%{$query}%")
                ->orWhere('last_name_ar', 'like', "%{$query}%")
                ->orWhere('first_name_ckb', 'like', "%{$query}%")
                ->orWhere('last_name_ckb', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('mobile', 'like', "%{$query}%");
        })->get();
    }

    /**
     * Get organization hierarchy.
     */
    public function getOrganizationHierarchy(): array
    {
        $departments = \Gopos\Models\Department::root()
            ->with(['children', 'employees' => function ($q) {
                $q->active()->with('position');
            }])
            ->get();

        return $this->buildHierarchy($departments);
    }

    /**
     * Build hierarchy array.
     */
    protected function buildHierarchy(Collection $departments): array
    {
        $result = [];

        foreach ($departments as $department) {
            $node = [
                'type' => 'department',
                'id' => $department->id,
                'name' => $department->localized_name,
                'manager' => $department->manager?->name,
                'employee_count' => $department->employees->count(),
                'children' => [],
            ];

            // Add employees
            foreach ($department->employees as $employee) {
                $node['children'][] = [
                    'type' => 'employee',
                    'id' => $employee->id,
                    'name' => $employee->localized_full_name,
                    'position' => $employee->position?->localized_title,
                    'photo' => $employee->photo,
                ];
            }

            // Add sub-departments
            if ($department->children->isNotEmpty()) {
                $node['children'] = array_merge(
                    $node['children'],
                    $this->buildHierarchy($department->children)
                );
            }

            $result[] = $node;
        }

        return $result;
    }

    /**
     * Export employees to array.
     */
    public function exportEmployees(?Collection $employees = null): array
    {
        $employees = $employees ?? Employee::with(['department', 'position', 'currency'])->get();

        return $employees->map(function ($employee) {
            return [
                'employee_number' => $employee->employee_number,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'mobile' => $employee->mobile,
                'department' => $employee->department?->name,
                'position' => $employee->position?->title,
                'hire_date' => $employee->hire_date?->format('Y-m-d'),
                'employment_type' => $employee->employment_type,
                'status' => $employee->status,
                'basic_salary' => $employee->basic_salary,
                'currency' => $employee->currency?->code,
            ];
        })->toArray();
    }
}
