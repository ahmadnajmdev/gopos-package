# Human Resources (HR) Module

## Overview

The HR module provides complete employee lifecycle management - from hiring to payroll. Manage organizational structure, track attendance, process leave requests, and run payroll with automatic calculations. Designed for the Iraq/Kurdistan market with trilingual support (English, Arabic, Kurdish Sorani).

---

## Why Use These Features?

### Key Benefits

| Benefit | How It Helps Your Business |
|---------|---------------------------|
| **Centralized Employee Data** | All employee information in one place |
| **Automated Attendance** | Track hours, late arrivals, early departures |
| **Leave Management** | Request, approve, track leave balances |
| **Payroll Processing** | Calculate salaries, deductions, generate payslips |
| **Compliance** | Track contracts, documents, legal requirements |
| **Self-Service** | Employees can view their own information |

### Problems It Solves

- **"Employee records are scattered everywhere"** - Centralized employee database
- **"We track attendance manually on paper"** - Digital attendance tracking
- **"Leave balance calculations are confusing"** - Automatic balance tracking
- **"Payroll takes days to process"** - Automated payroll calculations
- **"We forget about expiring documents"** - Document expiry alerts
- **"Employees keep asking HR for payslips"** - Self-service access

---

## Who Should Read This?

| Role | Relevant Sections |
|------|-------------------|
| **HR Managers** | All sections |
| **HR Staff** | Employees, Attendance, Leave |
| **Payroll Officers** | Payroll, Employee Loans |
| **Department Managers** | Attendance, Leave Approval |
| **Employees** | Self-service features |

---

## Module Components

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          HR Module                                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌────────────────────┐   Departments, positions, reporting structure   │
│  │   Organization     │   USE WHEN: Setting up company structure        │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Employee profiles, contracts, documents       │
│  │    Employees       │   USE WHEN: Managing employee information       │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Clock in/out, work hours, schedules           │
│  │    Attendance      │   USE WHEN: Tracking daily work hours           │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Leave requests, approvals, balances           │
│  │ Leave Management   │   USE WHEN: Processing time-off requests        │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Salary calculation, payslips, components      │
│  │     Payroll        │   USE WHEN: Monthly salary processing           │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Employee loans and repayment tracking         │
│  │  Employee Loans    │   USE WHEN: Managing salary advances/loans      │
│  └────────────────────┘                                                 │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Feature 1: Organization Structure

### What Is It?

Organization structure defines your company hierarchy - departments, positions, and reporting relationships. This forms the foundation for all HR operations.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Clear Hierarchy** | Who reports to whom |
| **Department Tracking** | Group employees by department |
| **Position Management** | Define job titles and grades |
| **Budget Alignment** | Link to cost centers for accounting |
| **Scalability** | Grows with your organization |

### When to Use

| Scenario | Action |
|----------|--------|
| Setting up HR for first time | Create departments and positions |
| New department created | Add department to structure |
| New job title needed | Create position |
| Reorganization | Update reporting relationships |
| Hiring employees | Assign to department and position |

### Use Case: Setting Up Company Structure

**Scenario:** Retail company with 3 stores

**Structure:**
```
Company
├── Administration
│   ├── HR Department
│   └── Finance Department
├── Operations
│   ├── Store A
│   ├── Store B
│   └── Store C
└── Warehouse
```

**Positions:**
- Store Manager
- Cashier
- Warehouse Staff
- HR Officer
- Accountant

### How to Use (UI Steps)

#### Creating Departments

1. Navigate to **HR > Departments**
2. Click **Create Department**
3. Enter:
   - **Name** (in all languages)
   - **Code** (e.g., HR-001)
   - **Parent Department** (for hierarchy)
   - **Manager** (head of department)
   - **Cost Center** (for accounting)
4. Click **Create**

#### Creating Positions

1. Navigate to **HR > Positions**
2. Click **Create Position**
3. Enter:
   - **Title** (in all languages)
   - **Code** (e.g., POS-CASHIER)
   - **Department** (where position belongs)
   - **Grade/Level** (for salary bands)
   - **Description** (job responsibilities)
4. Click **Create**

### How to Use (Code)

```php
use App\Models\Department;
use App\Models\Position;

// Create a department
$salesDept = Department::create([
    'name' => 'Sales Department',
    'name_ar' => 'قسم المبيعات',
    'name_ckb' => 'بەشی فرۆشتن',
    'code' => 'DEPT-SALES',
    'parent_id' => $operationsDept->id,
    'manager_id' => $manager->id,
    'cost_center_id' => $costCenter->id,
    'is_active' => true,
]);

// Create a position
$position = Position::create([
    'title' => 'Sales Representative',
    'title_ar' => 'مندوب مبيعات',
    'title_ckb' => 'نوێنەری فرۆشتن',
    'code' => 'POS-SALES-REP',
    'department_id' => $salesDept->id,
    'grade' => 'B',
    'min_salary' => 500000,
    'max_salary' => 800000,
    'description' => 'Responsible for customer sales...',
    'is_active' => true,
]);

// Get department hierarchy
$departments = Department::with('children', 'employees')->get();

// Get positions in a department
$positions = Position::where('department_id', $salesDept->id)->get();
```

### Best Practices

| Do | Don't |
|----|-------|
| Set up structure before adding employees | Add employees without structure |
| Assign managers to departments | Leave departments without heads |
| Use consistent code naming | Use random codes |
| Link to cost centers | Ignore accounting integration |

---

## Feature 2: Employee Management

### What Is It?

Employee management stores all information about your staff - personal details, employment info, contracts, qualifications, and documents. It's the central employee database.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Complete Records** | Everything about an employee in one place |
| **Contract Tracking** | Employment contracts with dates |
| **Document Storage** | ID, certificates, licenses |
| **Emergency Contacts** | Quick access to contacts |
| **History** | Track position and salary changes |

### When to Use

| Scenario | Action |
|----------|--------|
| Hiring new employee | Create employee record |
| Employee promotion | Update position and salary |
| Contract renewal | Update contract dates |
| Document expires | Upload new document |
| Employee leaves | Mark as inactive, record end date |

### Use Case: Onboarding New Employee

**Scenario:** Hiring Ahmed as new Sales Representative

**Steps:**
1. Create employee record with personal info
2. Set employment details (start date, department, position)
3. Set salary and payment info
4. Upload documents (ID, photo, certificates)
5. Create user account for system access
6. Assign to work schedule
7. Initialize leave balances

### How to Use (UI Steps)

#### Creating an Employee

1. Navigate to **HR > Employees**
2. Click **Create Employee**
3. **Personal Information:**
   - Name (all languages), Date of Birth, Gender
   - National ID, Phone, Email
   - Address, Emergency Contact
4. **Employment Details:**
   - Employee Code (auto or manual)
   - Department and Position
   - Employment Type (Full-time, Part-time, Contract)
   - Start Date, Contract End Date
   - Manager (who they report to)
5. **Compensation:**
   - Basic Salary
   - Payment Method (Cash, Bank Transfer)
   - Bank Account (if applicable)
6. **Documents:**
   - Upload ID copy, photo, certificates
   - Set expiry dates for documents
7. Click **Create**

### How to Use (Code)

```php
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\HR\EmployeeService;

$employeeService = app(EmployeeService::class);

// Create employee
$employee = $employeeService->createEmployee([
    // Personal info
    'first_name' => 'Ahmed',
    'first_name_ar' => 'أحمد',
    'last_name' => 'Hassan',
    'last_name_ar' => 'حسن',
    'date_of_birth' => '1990-05-15',
    'gender' => 'male',
    'national_id' => '12345678901234',
    'phone' => '+964 750 123 4567',
    'email' => 'ahmed@company.com',

    // Employment
    'employee_code' => 'EMP-001',
    'department_id' => $salesDept->id,
    'position_id' => $salesRep->id,
    'employment_type' => 'full_time',
    'hire_date' => '2026-01-15',
    'basic_salary' => 600000,
]);

// Employment types
Employee::TYPE_FULL_TIME;   // Full-time permanent
Employee::TYPE_PART_TIME;   // Part-time
Employee::TYPE_CONTRACT;    // Fixed-term contract
Employee::TYPE_TEMPORARY;   // Temporary worker
Employee::TYPE_INTERN;      // Intern

// Employment statuses
Employee::STATUS_ACTIVE;      // Currently employed
Employee::STATUS_ON_LEAVE;    // On extended leave
Employee::STATUS_SUSPENDED;   // Temporarily suspended
Employee::STATUS_TERMINATED;  // Employment ended
Employee::STATUS_RESIGNED;    // Employee resigned

// Add document
EmployeeDocument::create([
    'employee_id' => $employee->id,
    'type' => 'national_id',
    'name' => 'National ID Card',
    'file_path' => 'documents/emp-001/national-id.pdf',
    'expiry_date' => '2030-01-01',
]);

// Get employees with expiring documents
$expiringDocs = $employeeService->getExpiringContracts(days: 30);
```

### Best Practices

| Do | Don't |
|----|-------|
| Complete all required fields | Leave records incomplete |
| Upload document copies | Keep only paper records |
| Set document expiry dates | Forget to renew documents |
| Update records promptly | Let records become stale |

---

## Feature 3: Attendance Tracking

### What Is It?

Attendance tracking records when employees start and end work each day. It automatically calculates work hours, overtime, late arrivals, and early departures based on work schedules.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Accurate Records** | Know exactly when employees work |
| **Automatic Calculations** | System calculates hours, late, overtime |
| **Work Schedule Integration** | Compare against expected schedule |
| **Payroll Connection** | Attendance feeds into payroll |
| **Multiple Methods** | Manual entry, clock in/out, import |

### When to Use

| Scenario | Action |
|----------|--------|
| Employee arrives at work | Record clock-in |
| Employee leaves work | Record clock-out |
| Reviewing monthly attendance | Generate attendance report |
| Processing payroll | Use attendance data |

### Use Case: Daily Attendance Recording

**Scenario:** Track daily attendance for store employees

**Work Schedule:** 9:00 AM - 5:00 PM (8 hours)

**Ahmed's Attendance:**
- Clock in: 9:15 AM (15 minutes late)
- Clock out: 6:00 PM (1 hour overtime)
- Work hours: 8:45
- Regular hours: 8
- Overtime: 0:45

### How to Use (UI Steps)

#### Setting Up Work Schedules

1. Navigate to **HR > Work Schedules**
2. Click **Create Schedule**
3. Enter:
   - **Name** (e.g., "Standard Office Hours")
   - **Work Days** (Sun-Thu for Iraq)
   - **Start Time** and **End Time**
   - **Break Duration** (lunch break)
   - **Grace Period** (minutes before marked late)
4. Click **Create**

#### Recording Attendance

**Manual Entry:**
1. Navigate to **HR > Attendance**
2. Click **Add Attendance**
3. Select employee and date
4. Enter clock-in and clock-out times
5. Save

**Employee Self-Service:**
1. Employee clicks **Clock In** when arriving
2. System records current time
3. Employee clicks **Clock Out** when leaving

### How to Use (Code)

```php
use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Services\HR\AttendanceService;

$attendanceService = app(AttendanceService::class);

// Create work schedule
$schedule = WorkSchedule::create([
    'name' => 'Standard Office Hours',
    'name_ar' => 'ساعات العمل الرسمية',
    'name_ckb' => 'کاتژمێری کاری فەرمی',
    'work_days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'],
    'start_time' => '09:00',
    'end_time' => '17:00',
    'break_duration' => 60,
    'late_tolerance' => 15,
    'is_active' => true,
]);

// Record clock-in
$attendance = $attendanceService->clockIn($employee, 'Office', '192.168.1.1');

// Record clock-out
$attendanceService->clockOut($employee, 'Office', '192.168.1.1');

// Attendance statuses
Attendance::STATUS_PRESENT;  // Normal attendance
Attendance::STATUS_ABSENT;   // Did not come
Attendance::STATUS_LATE;     // Arrived late
Attendance::STATUS_LEAVE;    // On approved leave
Attendance::STATUS_HOLIDAY;  // Public holiday

// Get attendance report
$report = $attendanceService->getEmployeeSummary($employee, 2026, 1);
// Returns: days_present, days_absent, total_hours, overtime_hours, late_count
```

### Best Practices

| Do | Don't |
|----|-------|
| Set up schedules before tracking | Track without schedules |
| Record attendance daily | Batch enter at month end |
| Investigate absent days | Ignore unexplained absences |
| Review before payroll | Process payroll blindly |

---

## Feature 4: Leave Management

### What Is It?

Leave management handles employee time-off requests - from submission to approval to balance tracking. Supports multiple leave types with configurable policies.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Self-Service Requests** | Employees submit their own requests |
| **Approval Workflow** | Manager approval with notifications |
| **Balance Tracking** | Automatic balance calculations |
| **Policy Enforcement** | Enforce leave rules automatically |
| **Calendar View** | See who's off when |

### Leave Types

| Type | Description | Typical Policy |
|------|-------------|----------------|
| **Annual** | Vacation/holiday | 21-30 days/year |
| **Sick** | Medical leave | As needed with documentation |
| **Unpaid** | Leave without pay | As approved |
| **Maternity** | Childbirth leave | 70 days (Iraq law) |
| **Emergency** | Urgent situations | 3-5 days |

### When to Use

| Scenario | Action |
|----------|--------|
| Employee needs time off | Submit leave request |
| Manager reviews request | Approve or reject |
| New year begins | Initialize balances |
| Checking coverage | View leave calendar |

### Use Case: Annual Leave Request

**Scenario:** Ahmed requests 5 days vacation

**Process:**
1. Ahmed submits leave request (Jan 20-24)
2. System checks balance (Has 21 days)
3. Manager receives notification
4. Manager approves
5. Ahmed's balance: 21 → 16 days remaining

### How to Use (Code)

```php
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Services\HR\LeaveService;

$leaveService = app(LeaveService::class);

// Create leave type
$annualLeave = LeaveType::create([
    'name' => 'Annual Leave',
    'name_ar' => 'إجازة سنوية',
    'name_ckb' => 'مۆڵەتی ساڵانە',
    'code' => 'ANNUAL',
    'default_days' => 21,
    'is_paid' => true,
    'can_carry_forward' => true,
    'max_carry_forward' => 5,
]);

// Submit leave request
$request = $leaveService->submitRequest(
    employee: $employee,
    leaveTypeId: $annualLeave->id,
    startDate: Carbon::parse('2026-02-15'),
    endDate: Carbon::parse('2026-02-19'),
    reason: 'Family vacation'
);

// Approve request
$leaveService->approveRequest($request, $manager->id);

// Reject request
$leaveService->rejectRequest($request, $manager->id, 'Insufficient coverage');

// Get employee leave summary
$summary = $leaveService->getEmployeeSummary($employee, 2026);
// Returns: entitled, used, pending, available per leave type
```

### Best Practices

| Do | Don't |
|----|-------|
| Initialize balances annually | Forget to set up balances |
| Require documentation for sick leave | Accept without proof |
| Review pending requests daily | Let requests pile up |

---

## Feature 5: Payroll Processing

### What Is It?

Payroll processing calculates employee salaries based on attendance, leave, allowances, deductions, and loan repayments. Generates payslips and optionally posts to accounting.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Automated Calculations** | No manual salary calculations |
| **Component-Based** | Flexible allowances and deductions |
| **Attendance Integration** | Pulls attendance data automatically |
| **Loan Deductions** | Automatic loan repayments |
| **Payslip Generation** | Professional payslips |

### When to Use

| Scenario | Action |
|----------|--------|
| End of month | Create payroll period |
| Reviewing payroll | Generate payslips |
| Everything verified | Approve payroll |
| Paying employees | Mark as paid |

### Use Case: Monthly Payroll

**Scenario:** Processing January 2026 payroll

**Process:**
1. Create payroll period (Jan 1-31, 2026)
2. System calculates for each employee:
   - Basic salary
   - Attendance adjustments
   - Allowances
   - Deductions and loans
   - Net salary
3. Review payslips
4. Approve payroll
5. Generate bank file for payment

### How to Use (Code)

```php
use App\Models\PayrollPeriod;
use App\Models\PayrollComponent;
use App\Services\HR\PayrollService;

$payrollService = app(PayrollService::class);

// Create payroll component
$transport = PayrollComponent::create([
    'name' => 'Transport Allowance',
    'name_ar' => 'بدل النقل',
    'name_ckb' => 'کرێی گواستنەوە',
    'code' => 'TRANSPORT',
    'type' => 'earning', // earning or deduction
    'calculation_type' => 'fixed',
    'amount' => 50000,
    'is_taxable' => true,
]);

// Create payroll period
$period = $payrollService->createPeriod(2026, 1);

// Process payroll for all employees
$payrollService->processPayroll($period);

// Approve payroll
$payrollService->approvePayroll($period, $approver->id);

// Mark as paid
$payrollService->markAsPaid($period, $payer->id);

// Generate bank file for transfers
$bankFile = $payrollService->generateBankFile($period, 'csv');
```

### Best Practices

| Do | Don't |
|----|-------|
| Set up components first | Add components mid-period |
| Review every payslip | Approve without checking |
| Run payroll monthly | Delay payroll processing |

---

## Feature 6: Employee Loans

### What Is It?

Employee loans track advances and loans given to employees, with automatic repayment deductions from their salaries.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Loan Tracking** | Complete loan history |
| **Automatic Deductions** | Monthly deductions from salary |
| **Balance Visibility** | Track remaining balance |

### Use Case: Salary Advance

**Scenario:** Ahmed requests 500,000 IQD advance over 5 months

1. Create loan: 500,000 IQD, 5 installments
2. Each payroll: Deduct 100,000 IQD
3. After 5 months: Loan completed

### How to Use (Code)

```php
use App\Models\EmployeeLoan;
use App\Models\LoanRepayment;

// Create employee loan
$loan = EmployeeLoan::create([
    'employee_id' => $employee->id,
    'loan_type' => 'salary_advance',
    'amount' => 500000,
    'installments' => 5,
    'monthly_installment' => 100000,
    'start_date' => '2026-02-01',
    'status' => 'active',
    'approved_by' => $manager->id,
]);

// Check remaining balance
$remaining = $loan->remaining_balance;
```

---

## Technical Reference

### Services

| Service | Purpose |
|---------|---------|
| `AttendanceService` | Clock in/out, attendance reports |
| `LeaveService` | Leave requests, balances, approvals |
| `PayrollService` | Payroll processing, payslips |
| `EmployeeService` | Employee CRUD, transfers, terminations |

### Database Tables

| Table | Purpose |
|-------|---------|
| departments | Organization structure |
| positions | Job positions |
| employees | Employee records |
| work_schedules | Working hour definitions |
| attendances | Daily attendance records |
| holidays | Public holiday calendar |
| leave_types | Leave type definitions |
| leave_balances | Employee leave balances |
| leave_requests | Leave request workflow |
| payroll_components | Salary components |
| payroll_periods | Monthly payroll cycles |
| payslips | Employee payslips |
| employee_loans | Loan tracking |

---

## Troubleshooting

### Attendance not calculating correctly

**Solutions:**
1. Verify work schedule is assigned
2. Check schedule times are correct
3. Verify clock times are recorded

---

### Leave balance is wrong

**Solutions:**
1. Check initial balance was set
2. Verify approved leaves were deducted
3. Check carry-forward from previous year

---

### Payroll deduction missing

**Solutions:**
1. Verify component is assigned to employee
2. Check component is active
3. Regenerate payslip after changes

---

*Last updated: January 2026*
