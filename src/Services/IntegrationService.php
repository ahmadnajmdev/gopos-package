<?php

namespace Gopos\Services;

use Gopos\Models\Employee;
use Gopos\Models\PayrollPeriod;
use Gopos\Models\Payslip;
use Gopos\Models\Sale;
use Gopos\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntegrationService
{
    public function __construct(
        protected GeneralLedgerService $glService,
        protected InventoryService $inventoryService,
        protected TaxCalculationService $taxService,
    ) {}

    /**
     * Process a sale with all integrations (inventory, accounting)
     */
    public function processSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            // 1. Update inventory for each item
            $this->updateInventoryForSale($sale);

            // 2. Create GL entries for the sale
            $this->postSaleToGL($sale);

            // 3. Update customer balance if applicable
            if ($sale->customer_id && $sale->payment_status !== 'paid') {
                $this->updateCustomerBalance($sale);
            }

            Log::info('Sale processed with integrations', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
            ]);
        });
    }

    /**
     * Update inventory when a sale is created
     */
    protected function updateInventoryForSale(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $this->inventoryService->reduceStock(
                productId: $item->product_id,
                quantity: $item->quantity,
                referenceType: 'sale',
                referenceId: $sale->id,
                warehouseId: $sale->warehouse_id ?? null,
                notes: "Sale #{$sale->sale_number}"
            );
        }
    }

    /**
     * Post sale to General Ledger
     */
    protected function postSaleToGL(Sale $sale): void
    {
        $entries = [];

        // Debit: Cash/Accounts Receivable
        $debitAccount = $sale->payment_status === 'paid'
            ? $this->glService->getAccountByCode('1001') // Cash
            : $this->glService->getAccountByCode('1200'); // Accounts Receivable

        $entries[] = [
            'account_id' => $debitAccount->id,
            'debit' => $sale->total_amount,
            'credit' => 0,
            'description' => "Sale #{$sale->sale_number}",
        ];

        // Credit: Sales Revenue
        $salesAccount = $this->glService->getAccountByCode('4001');
        $entries[] = [
            'account_id' => $salesAccount->id,
            'debit' => 0,
            'credit' => $sale->sub_total,
            'description' => "Sales revenue - #{$sale->sale_number}",
        ];

        // Credit: Tax Payable (if applicable)
        if ($sale->tax_amount > 0) {
            $taxAccount = $this->glService->getAccountByCode('2100');
            $entries[] = [
                'account_id' => $taxAccount->id,
                'debit' => 0,
                'credit' => $sale->tax_amount,
                'description' => "Tax payable - Sale #{$sale->sale_number}",
            ];
        }

        // Credit: Discount (if applicable)
        if ($sale->discount_amount > 0) {
            $discountAccount = $this->glService->getAccountByCode('4002');
            $entries[] = [
                'account_id' => $discountAccount->id,
                'debit' => $sale->discount_amount,
                'credit' => 0,
                'description' => "Sales discount - #{$sale->sale_number}",
            ];
        }

        // Post COGS entry
        $this->postCOGSEntry($sale);

        $this->glService->createJournalEntry(
            date: $sale->sale_date,
            description: "Sale #{$sale->sale_number}",
            lines: $entries,
            referenceType: 'sale',
            referenceId: $sale->id
        );
    }

    /**
     * Post Cost of Goods Sold entry
     */
    protected function postCOGSEntry(Sale $sale): void
    {
        $totalCost = 0;

        foreach ($sale->items as $item) {
            $product = $item->product;
            $totalCost += $product->cost * $item->quantity;
        }

        if ($totalCost <= 0) {
            return;
        }

        $cogsAccount = $this->glService->getAccountByCode('5001');
        $inventoryAccount = $this->glService->getAccountByCode('1300');

        $this->glService->createJournalEntry(
            date: $sale->sale_date,
            description: "COGS for Sale #{$sale->sale_number}",
            lines: [
                [
                    'account_id' => $cogsAccount->id,
                    'debit' => $totalCost,
                    'credit' => 0,
                    'description' => 'Cost of goods sold',
                ],
                [
                    'account_id' => $inventoryAccount->id,
                    'debit' => 0,
                    'credit' => $totalCost,
                    'description' => 'Inventory reduction',
                ],
            ],
            referenceType: 'sale_cogs',
            referenceId: $sale->id
        );
    }

    /**
     * Update customer balance for credit sales
     */
    protected function updateCustomerBalance(Sale $sale): void
    {
        $customer = $sale->customer;
        if ($customer) {
            $customer->increment('balance', $sale->total_amount - $sale->paid_amount);
        }
    }

    /**
     * Process sale return with all integrations
     */
    public function processSaleReturn(\Gopos\Models\SaleReturn $return): void
    {
        DB::transaction(function () use ($return) {
            // 1. Restore inventory
            foreach ($return->items as $item) {
                $this->inventoryService->addStock(
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    referenceType: 'sale_return',
                    referenceId: $return->id,
                    warehouseId: $return->sale->warehouse_id ?? null,
                    notes: "Return #{$return->return_number}"
                );
            }

            // 2. Reverse GL entries
            $this->reverseSaleGLEntries($return);

            Log::info('Sale return processed', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
            ]);
        });
    }

    /**
     * Reverse GL entries for sale return
     */
    protected function reverseSaleGLEntries(\Gopos\Models\SaleReturn $return): void
    {
        $cashAccount = $this->glService->getAccountByCode('1001');
        $salesReturnAccount = $this->glService->getAccountByCode('4003');
        $inventoryAccount = $this->glService->getAccountByCode('1300');
        $cogsAccount = $this->glService->getAccountByCode('5001');

        // Reverse sales entry
        $entries = [
            [
                'account_id' => $salesReturnAccount->id,
                'debit' => $return->total_amount,
                'credit' => 0,
                'description' => "Sales return #{$return->return_number}",
            ],
            [
                'account_id' => $cashAccount->id,
                'debit' => 0,
                'credit' => $return->total_amount,
                'description' => "Refund for return #{$return->return_number}",
            ],
        ];

        $this->glService->createJournalEntry(
            date: $return->return_date,
            description: "Sales Return #{$return->return_number}",
            lines: $entries,
            referenceType: 'sale_return',
            referenceId: $return->id
        );

        // Reverse COGS
        $totalCost = 0;
        foreach ($return->items as $item) {
            $totalCost += $item->product->cost * $item->quantity;
        }

        if ($totalCost > 0) {
            $this->glService->createJournalEntry(
                date: $return->return_date,
                description: "COGS Reversal for Return #{$return->return_number}",
                lines: [
                    [
                        'account_id' => $inventoryAccount->id,
                        'debit' => $totalCost,
                        'credit' => 0,
                        'description' => 'Inventory restored',
                    ],
                    [
                        'account_id' => $cogsAccount->id,
                        'debit' => 0,
                        'credit' => $totalCost,
                        'description' => 'COGS reversed',
                    ],
                ],
                referenceType: 'sale_return_cogs',
                referenceId: $return->id
            );
        }
    }

    /**
     * Process payroll and post to GL
     */
    public function processPayroll(PayrollPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            // 1. Calculate payroll for all employees
            $this->calculatePayrollForPeriod($period);

            // 2. Post payroll to GL
            $this->postPayrollToGL($period);

            // 3. Update loan deductions
            $this->processLoanDeductions($period);

            $period->update([
                'status' => 'processed',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            Log::info('Payroll processed', [
                'period_id' => $period->id,
                'period_name' => $period->name,
            ]);
        });
    }

    /**
     * Calculate payroll for all active employees
     */
    protected function calculatePayrollForPeriod(PayrollPeriod $period): void
    {
        $employees = Employee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            $payslip = $this->calculateEmployeePayslip($employee, $period);
            $period->payslips()->save($payslip);
        }

        $period->update([
            'total_employees' => $employees->count(),
            'total_gross' => $period->payslips()->sum('gross_salary'),
            'total_deductions' => $period->payslips()->sum('total_deductions'),
            'total_net' => $period->payslips()->sum('net_salary'),
        ]);
    }

    /**
     * Calculate individual employee payslip
     */
    protected function calculateEmployeePayslip(Employee $employee, PayrollPeriod $period): Payslip
    {
        $basicSalary = $employee->basic_salary;
        $earnings = $basicSalary;
        $deductions = 0;

        // Add overtime
        $overtimeHours = $this->getOvertimeHours($employee, $period);
        $overtimeRate = $basicSalary / 30 / 8 * 1.5; // 1.5x hourly rate
        $overtimeAmount = $overtimeHours * $overtimeRate;
        $earnings += $overtimeAmount;

        // Apply salary components
        $components = $employee->salaryComponents;
        foreach ($components as $component) {
            if ($component->type === 'earning') {
                if ($component->calculation_type === 'percentage') {
                    $earnings += $basicSalary * ($component->pivot->amount / 100);
                } else {
                    $earnings += $component->pivot->amount;
                }
            } else {
                if ($component->calculation_type === 'percentage') {
                    $deductions += $basicSalary * ($component->pivot->amount / 100);
                } else {
                    $deductions += $component->pivot->amount;
                }
            }
        }

        // Calculate absent deductions
        $absentDays = $this->getAbsentDays($employee, $period);
        $dailyRate = $basicSalary / 30;
        $absentDeduction = $absentDays * $dailyRate;
        $deductions += $absentDeduction;

        // Get loan deduction
        $loanDeduction = $this->getLoanDeduction($employee);
        $deductions += $loanDeduction;

        $grossSalary = $earnings;
        $netSalary = $grossSalary - $deductions;

        return new Payslip([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary' => $basicSalary,
            'gross_salary' => $grossSalary,
            'total_earnings' => $earnings,
            'total_deductions' => $deductions,
            'net_salary' => $netSalary,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'absent_days' => $absentDays,
            'absent_deduction' => $absentDeduction,
            'loan_deduction' => $loanDeduction,
            'status' => 'draft',
        ]);
    }

    /**
     * Get overtime hours for employee in period
     */
    protected function getOvertimeHours(Employee $employee, PayrollPeriod $period): float
    {
        return $employee->attendanceRecords()
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->sum('overtime_hours') ?? 0;
    }

    /**
     * Get absent days for employee in period
     */
    protected function getAbsentDays(Employee $employee, PayrollPeriod $period): int
    {
        return $employee->attendanceRecords()
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->where('status', 'absent')
            ->count();
    }

    /**
     * Get loan deduction amount for employee
     */
    protected function getLoanDeduction(Employee $employee): float
    {
        $activeLoans = $employee->loans()
            ->where('status', 'active')
            ->get();

        $totalDeduction = 0;
        foreach ($activeLoans as $loan) {
            $totalDeduction += $loan->installment_amount;
        }

        return $totalDeduction;
    }

    /**
     * Post payroll to General Ledger
     */
    protected function postPayrollToGL(PayrollPeriod $period): void
    {
        $salaryExpenseAccount = $this->glService->getAccountByCode('6001');
        $cashAccount = $this->glService->getAccountByCode('1001');
        $loansReceivableAccount = $this->glService->getAccountByCode('1250');

        $entries = [];

        // Debit: Salary Expense
        $entries[] = [
            'account_id' => $salaryExpenseAccount->id,
            'debit' => $period->total_gross,
            'credit' => 0,
            'description' => "Salary expense for {$period->name}",
        ];

        // Credit: Cash (net payroll)
        $entries[] = [
            'account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => $period->total_net,
            'description' => "Net payroll payment for {$period->name}",
        ];

        // Credit: Loans Receivable (loan deductions)
        $totalLoanDeductions = $period->payslips()->sum('loan_deduction');
        if ($totalLoanDeductions > 0) {
            $entries[] = [
                'account_id' => $loansReceivableAccount->id,
                'debit' => 0,
                'credit' => $totalLoanDeductions,
                'description' => "Loan deductions for {$period->name}",
            ];
        }

        // Other deductions as liabilities
        $otherDeductions = $period->total_deductions - $totalLoanDeductions;
        if ($otherDeductions > 0) {
            $deductionsPayableAccount = $this->glService->getAccountByCode('2200');
            $entries[] = [
                'account_id' => $deductionsPayableAccount->id,
                'debit' => 0,
                'credit' => $otherDeductions,
                'description' => "Payroll deductions payable for {$period->name}",
            ];
        }

        $this->glService->createJournalEntry(
            date: $period->payment_date,
            description: "Payroll for {$period->name}",
            lines: $entries,
            referenceType: 'payroll',
            referenceId: $period->id
        );
    }

    /**
     * Process loan deductions and update loan balances
     */
    protected function processLoanDeductions(PayrollPeriod $period): void
    {
        foreach ($period->payslips as $payslip) {
            if ($payslip->loan_deduction > 0) {
                $employee = $payslip->employee;
                $activeLoans = $employee->loans()->where('status', 'active')->get();

                foreach ($activeLoans as $loan) {
                    // Record repayment
                    $loan->repayments()->create([
                        'amount' => $loan->installment_amount,
                        'payment_date' => $period->payment_date,
                        'payroll_period_id' => $period->id,
                        'payslip_id' => $payslip->id,
                    ]);

                    // Update loan balance
                    $loan->increment('paid_installments');
                    $loan->decrement('remaining_amount', $loan->installment_amount);

                    // Check if loan is completed
                    if ($loan->paid_installments >= $loan->installments) {
                        $loan->update(['status' => 'completed']);
                    }
                }
            }
        }
    }

    /**
     * Process leave request approval
     */
    public function processLeaveApproval(\Gopos\Models\LeaveRequest $request): void
    {
        DB::transaction(function () use ($request) {
            // Update leave balance
            $balance = $request->employee->leaveBalances()
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $request->start_date->year)
                ->first();

            if ($balance) {
                $balance->decrement('available_days', $request->days);
                $balance->increment('used_days', $request->days);
            }

            Log::info('Leave request approved', [
                'request_id' => $request->id,
                'employee_id' => $request->employee_id,
                'days' => $request->days,
            ]);
        });
    }

    /**
     * Link employee to user account
     */
    public function linkEmployeeToUser(Employee $employee, User $user): void
    {
        $employee->update(['user_id' => $user->id]);
        $user->update(['employee_id' => $employee->id]);

        Log::info('Employee linked to user', [
            'employee_id' => $employee->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Process stock transfer completion
     */
    public function processStockTransfer(\Gopos\Models\StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Reduce from source warehouse
                $this->inventoryService->reduceStock(
                    productId: $item->product_id,
                    quantity: $item->quantity_sent,
                    referenceType: 'transfer_out',
                    referenceId: $transfer->id,
                    warehouseId: $transfer->from_warehouse_id,
                    notes: "Transfer #{$transfer->transfer_number} to {$transfer->toWarehouse->name}"
                );

                // Add to destination warehouse
                $this->inventoryService->addStock(
                    productId: $item->product_id,
                    quantity: $item->quantity_received,
                    referenceType: 'transfer_in',
                    referenceId: $transfer->id,
                    warehouseId: $transfer->to_warehouse_id,
                    notes: "Transfer #{$transfer->transfer_number} from {$transfer->fromWarehouse->name}"
                );
            }

            Log::info('Stock transfer completed', [
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number,
            ]);
        });
    }

    /**
     * Post stock count adjustments to GL
     */
    public function postStockCountAdjustments(\Gopos\Models\StockCount $count): void
    {
        DB::transaction(function () use ($count) {
            $totalAdjustmentValue = 0;

            foreach ($count->items as $item) {
                $variance = $item->counted_quantity - $item->system_quantity;
                if ($variance != 0) {
                    $adjustmentValue = $variance * $item->product->cost;
                    $totalAdjustmentValue += $adjustmentValue;

                    // Create inventory movement
                    $this->inventoryService->adjustStock(
                        productId: $item->product_id,
                        quantity: $variance,
                        referenceType: 'stock_count',
                        referenceId: $count->id,
                        warehouseId: $count->warehouse_id,
                        notes: "Stock count #{$count->count_number} adjustment"
                    );
                }
            }

            // Post to GL if there's an adjustment
            if ($totalAdjustmentValue != 0) {
                $inventoryAccount = $this->glService->getAccountByCode('1300');
                $adjustmentAccount = $this->glService->getAccountByCode('5099'); // Inventory Adjustment

                $entries = [];
                if ($totalAdjustmentValue > 0) {
                    // Inventory increase
                    $entries[] = [
                        'account_id' => $inventoryAccount->id,
                        'debit' => $totalAdjustmentValue,
                        'credit' => 0,
                        'description' => "Inventory increase from count #{$count->count_number}",
                    ];
                    $entries[] = [
                        'account_id' => $adjustmentAccount->id,
                        'debit' => 0,
                        'credit' => $totalAdjustmentValue,
                        'description' => 'Adjustment gain',
                    ];
                } else {
                    // Inventory decrease
                    $entries[] = [
                        'account_id' => $adjustmentAccount->id,
                        'debit' => abs($totalAdjustmentValue),
                        'credit' => 0,
                        'description' => 'Adjustment loss',
                    ];
                    $entries[] = [
                        'account_id' => $inventoryAccount->id,
                        'debit' => 0,
                        'credit' => abs($totalAdjustmentValue),
                        'description' => "Inventory decrease from count #{$count->count_number}",
                    ];
                }

                $this->glService->createJournalEntry(
                    date: now(),
                    description: "Stock Count Adjustment #{$count->count_number}",
                    lines: $entries,
                    referenceType: 'stock_count',
                    referenceId: $count->id
                );
            }

            Log::info('Stock count adjustments posted', [
                'count_id' => $count->id,
                'adjustment_value' => $totalAdjustmentValue,
            ]);
        });
    }
}
