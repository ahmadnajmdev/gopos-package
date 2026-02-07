# Accounting Module Enhancements

## Overview

The Accounting module provides enterprise-level financial management capabilities. Beyond basic bookkeeping, it includes bank reconciliation, budgeting with variance analysis, cost center tracking, and automated cash flow reporting - everything you need for professional financial management.

---

## Why Use These Features?

### Key Benefits

| Benefit | How It Helps Your Business |
|---------|---------------------------|
| **Bank Control** | Reconcile accounts monthly, catch errors early |
| **Budget Management** | Plan spending, track variances, control costs |
| **Cost Visibility** | Know what each department/project costs |
| **Cash Flow Insight** | Understand where your cash goes |
| **Audit Ready** | Complete records for auditors and tax authorities |

### Problems It Solves

- **"Bank statement doesn't match our books"** - Monthly reconciliation process
- **"We overspend without realizing"** - Budget variance alerts
- **"Which department is costing the most?"** - Cost center tracking
- **"Will we have cash next month?"** - Cash flow statements
- **"Our accountant uses spreadsheets for everything"** - All-in-one solution

---

## Who Should Read This?

| Role | Relevant Sections |
|------|-------------------|
| **Accountants** | All sections |
| **Finance Managers** | All sections, especially Budgeting |
| **Department Managers** | Cost Centers, Budget vs Actual |
| **Business Owners** | Cash Flow, Budget Overview |
| **Auditors** | Reconciliation, Audit Trail |

---

## Module Components

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      Accounting Enhancements                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌────────────────────┐   Track bank accounts and transactions          │
│  │  Bank Management   │   USE WHEN: Managing company bank accounts      │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Match bank statements with book records       │
│  │ Bank Reconciliation│   USE WHEN: Monthly closing, audit prep         │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Plan and track spending                       │
│  │  Budgeting System  │   USE WHEN: Annual planning, cost control       │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Track costs by department/project             │
│  │  Cost Centers      │   USE WHEN: Departmental accountability         │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Analyze cash inflows and outflows             │
│  │  Cash Flow Report  │   USE WHEN: Cash planning, investor reports     │
│  └────────────────────┘                                                 │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Feature 1: Bank Account Management

### What Is It?

Bank account management tracks all your company bank accounts within GoPOS. Record deposits, withdrawals, transfers, and fees - all linked to your general ledger accounts.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Centralized View** | All bank accounts in one place |
| **Real-time Balance** | Always know current balances |
| **Transaction History** | Complete record of all movements |
| **GL Integration** | Automatic link to chart of accounts |
| **Multi-currency** | Support for IQD, USD, and other currencies |

### When to Use

| Scenario | Action |
|----------|--------|
| Opening new bank account | Create bank account in system |
| Receiving customer payment | Record deposit |
| Paying supplier | Record withdrawal/check |
| Bank charges deducted | Record fee transaction |
| Interest earned | Record interest transaction |

### Use Case: Managing Multiple Bank Accounts

**Scenario:** Company has IQD and USD accounts at different banks

**Setup:**
1. Create account for each bank account
2. Link each to corresponding GL account
3. Set opening balances

**Daily Operations:**
1. Record deposits as money comes in
2. Record checks/withdrawals as payments go out
3. System updates balances automatically
4. End of month: reconcile with bank statements

### How to Use (UI Steps)

#### Creating a Bank Account

1. Navigate to **Accounting > Bank Accounts**
2. Click **Create Bank Account**
3. Fill in details:
   - **GL Account** (link to existing cash/bank account)
   - **Bank Name** (in all languages)
   - **Account Number**
   - **IBAN** (if applicable)
   - **Currency**
   - **Opening Balance**
4. Click **Create**

#### Recording Transactions

1. Open the bank account
2. Click **Add Transaction**
3. Select transaction type:
   - **Deposit** - Money coming in
   - **Withdrawal** - Money going out
   - **Transfer** - Between accounts
   - **Fee** - Bank charges
   - **Interest** - Interest earned
4. Enter amount, date, reference, description
5. Save

### How to Use (Code)

```php
use App\Models\BankAccount;
use App\Models\BankTransaction;

// Create a bank account
$bankAccount = BankAccount::create([
    'account_id' => $glAccount->id,
    'bank_name' => 'Central Bank of Iraq',
    'bank_name_ar' => 'البنك المركزي العراقي',
    'bank_name_ckb' => 'بانکی ناوەندی عێراق',
    'account_number' => '1234567890',
    'iban' => 'IQ12 3456 7890 1234 5678',
    'swift_code' => 'CBIQIQBA',
    'branch' => 'Erbil Main Branch',
    'currency_id' => $currency->id,
    'opening_balance' => 50000.00,
    'is_active' => true,
]);

// Get current balance
echo $bankAccount->current_balance;

// Record a deposit
$transaction = $bankAccount->recordTransaction([
    'type' => BankTransaction::TYPE_DEPOSIT,
    'description' => 'Customer payment received',
    'amount' => 5000.00,
    'transaction_date' => now(),
    'reference' => 'DEP-001',
    'status' => 'pending',
]);

// Record a withdrawal/check
$transaction = $bankAccount->recordTransaction([
    'type' => BankTransaction::TYPE_WITHDRAWAL,
    'description' => 'Supplier payment',
    'amount' => 2500.00,
    'transaction_date' => now(),
    'check_number' => 'CHK-0045',
    'payee' => 'ABC Suppliers',
]);

// Transaction types available
BankTransaction::TYPE_DEPOSIT;    // Incoming funds
BankTransaction::TYPE_WITHDRAWAL; // Outgoing funds
BankTransaction::TYPE_TRANSFER;   // Inter-bank transfer
BankTransaction::TYPE_FEE;        // Bank charges
BankTransaction::TYPE_INTEREST;   // Interest earned

// Mark transaction as cleared
$transaction->markCleared();
```

### Best Practices

| Do | Don't |
|----|-------|
| Record transactions daily | Wait until month-end |
| Use descriptive references | Leave descriptions blank |
| Link to GL accounts properly | Create disconnected accounts |
| Record bank fees promptly | Ignore small charges |

---

## Feature 2: Bank Reconciliation

### What Is It?

Bank reconciliation matches your book records with actual bank statements. It identifies outstanding checks, deposits in transit, bank fees, and errors - ensuring your books match reality.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Accuracy** | Catch errors before they compound |
| **Fraud Detection** | Identify unauthorized transactions |
| **Audit Trail** | Document monthly verification |
| **Clean Books** | Month-end closing with confidence |
| **Control** | Know exactly what cleared the bank |

### When to Use

| Scenario | Use Reconciliation |
|----------|-------------------|
| Monthly closing | Yes - required |
| Bank statement arrives | Yes - reconcile immediately |
| Year-end audit | Yes - ensure all months reconciled |
| Suspicious activity | Yes - identify discrepancies |
| Cash flow issues | Yes - find outstanding items |

### Use Case: Monthly Bank Reconciliation

**Scenario:** January bank statement arrived with ending balance 52,500 IQD

**Process:**
1. Create reconciliation for January
2. Enter statement balance: 52,500 IQD
3. System shows book balance: 55,000 IQD
4. Difference: 2,500 IQD
5. Identify:
   - Outstanding check #45: 3,000 IQD (not yet cashed)
   - Bank service fee: 500 IQD (not recorded)
6. Add items to reconciliation
7. New adjusted balance: 52,500 IQD (matches!)
8. Complete reconciliation

### Reconciliation Formula

```
Book Balance                           55,000
- Bank Charges (not yet recorded)        (500)
= Adjusted Book Balance                54,500

Statement Balance                      52,500
- Outstanding Checks                   (3,000)
+ Deposits in Transit                   5,000
= Adjusted Bank Balance                54,500

Difference: 0 (Reconciled!)
```

### How to Use (UI Steps)

#### Starting a Reconciliation

1. Navigate to **Accounting > Bank Reconciliation**
2. Click **New Reconciliation**
3. Select **Bank Account**
4. Enter:
   - **Statement Date** (e.g., January 31, 2026)
   - **Statement Balance** (from bank statement)
   - **Period Start/End**
5. Click **Create**

#### Working Through Items

1. System shows all unreconciled transactions
2. For each item on bank statement:
   - If it matches a book entry, mark as **Cleared**
   - If it's not in your books (bank fee), add as **Adjustment**
3. For book entries NOT on statement:
   - Mark as **Outstanding Check** or **Deposit in Transit**

#### Completing Reconciliation

1. View **Summary** showing:
   - Book balance vs adjusted book balance
   - Statement balance vs adjusted statement balance
   - Difference (should be 0)
2. If balanced, click **Complete Reconciliation**
3. System creates journal entries for adjustments
4. Transactions marked as reconciled

### How to Use (Code)

```php
use App\Services\BankReconciliationService;

$reconciliationService = app(BankReconciliationService::class);

// Create new reconciliation
$reconciliation = $reconciliationService->createReconciliation(
    bankAccount: $bankAccount,
    statementBalance: 52500.00,
    statementDate: '2026-01-31',
    statementStartDate: '2026-01-01',
    statementEndDate: '2026-01-31'
);

// Get outstanding checks
$outstandingChecks = $reconciliationService->getOutstandingChecks(
    $bankAccount,
    '2026-01-31'
);

// Add outstanding checks to reconciliation
foreach ($outstandingChecks as $check) {
    $reconciliationService->addOutstandingCheck($reconciliation, $check);
}

// Get deposits in transit
$depositsInTransit = $reconciliationService->getDepositsInTransit(
    $bankAccount,
    '2026-01-31'
);

// Add deposits in transit
foreach ($depositsInTransit as $deposit) {
    $reconciliationService->addDepositInTransit($reconciliation, $deposit);
}

// Add bank charges (creates adjustment)
$reconciliationService->addBankCharge(
    $reconciliation,
    25.00,
    'Monthly service fee'
);

// Add bank interest
$reconciliationService->addBankInterest(
    $reconciliation,
    15.50,
    'Interest earned'
);

// Calculate summary
$summary = $reconciliationService->calculateSummary($reconciliation);

echo "Book Balance: " . $summary['book_balance'];
echo "Adjusted Book Balance: " . $summary['adjusted_book_balance'];
echo "Statement Balance: " . $summary['statement_balance'];
echo "Outstanding Checks: " . $summary['outstanding_checks'];
echo "Deposits in Transit: " . $summary['deposits_in_transit'];
echo "Difference: " . $summary['difference'];
echo "Is Balanced: " . ($summary['is_balanced'] ? 'Yes' : 'No');

// Complete reconciliation
$reconciliationService->completeReconciliation(
    $reconciliation,
    bankChargeAccountId: $bankChargesExpenseAccountId,
    interestIncomeAccountId: $interestIncomeAccountId
);
```

### Best Practices

| Do | Don't |
|----|-------|
| Reconcile within first week of month | Wait until quarter-end |
| Investigate all differences | Ignore small amounts |
| Document adjustments clearly | Make unexplained entries |
| Keep bank statements on file | Discard statements |
| Complete before closing period | Close period unreconciled |

---

## Feature 3: Budgeting System

### What Is It?

The budgeting system lets you create annual budgets, track spending against budget, identify variances, and make adjustments. Compare planned vs actual spending by account and cost center.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Cost Control** | Set limits before spending happens |
| **Variance Alerts** | Know immediately when over budget |
| **Planning** | Annual planning by department |
| **Accountability** | Compare actual to plan |
| **Decision Support** | Data for resource allocation |

### When to Use

| Scenario | Use Budgeting |
|----------|--------------|
| Annual planning | Create next year's budget |
| Department spending | Allocate by cost center |
| Project budgeting | Create project budget |
| Monthly review | Check variance reports |
| Cost increases | Create budget revision |

### Use Case: Annual Operating Budget

**Scenario:** Planning 2026 operating budget

**Process:**
1. Create budget for fiscal year 2026
2. Add budget lines for each expense account
3. Assign amounts by month
4. Submit for management approval
5. Activate when approved
6. Monitor monthly vs actual

**Example Budget:**
```
Account                Jan      Feb      Mar     ...    Total
Salaries             50,000   50,000   50,000   ...   600,000
Rent                 10,000   10,000   10,000   ...   120,000
Utilities             2,000    2,000    2,500   ...    28,000
Marketing             5,000    3,000    8,000   ...    72,000
```

### Budget Types

| Type | Purpose | When to Use |
|------|---------|-------------|
| **Operating** | Day-to-day expenses | Annual expense planning |
| **Capital** | Asset purchases | Equipment, vehicles, buildings |
| **Cash Flow** | Cash projections | Cash planning |
| **Project** | Specific projects | Project-based spending |

### How to Use (UI Steps)

#### Creating a Budget

1. Navigate to **Accounting > Budgets**
2. Click **Create Budget**
3. Enter:
   - **Name** (in all languages)
   - **Fiscal Period** (year)
   - **Budget Type** (Operating, Capital, etc.)
4. Click **Create**

#### Adding Budget Lines

1. Open the budget
2. Click **Add Line**
3. Select:
   - **GL Account** (expense account)
   - **Cost Center** (optional)
4. Enter amounts for each month
5. Or use distribution tools:
   - **Distribute Evenly** - Same amount each month
   - **Distribute by Pattern** - Seasonal pattern
6. Add notes explaining the amount
7. Save

#### Approving a Budget

1. Once all lines added, click **Submit for Approval**
2. Manager reviews and clicks **Approve**
3. Click **Activate** to start tracking

#### Viewing Budget vs Actual

1. Go to **Reports > Budget vs Actual**
2. Select budget and period
3. View:
   - Budgeted amount per account
   - Actual spending
   - Variance (amount and %)
   - Over/under budget indicators

### How to Use (Code)

```php
use App\Services\BudgetService;
use App\Models\Budget;

$budgetService = app(BudgetService::class);

// Create an operating budget
$budget = $budgetService->createBudget(
    name: '2026 Operating Budget',
    fiscalPeriod: $fiscalPeriod,
    budgetType: Budget::TYPE_OPERATING,
    nameAr: 'ميزانية التشغيل 2026',
    nameCkb: 'بودجەی کارکردنی 2026'
);

// Add budget line with monthly amounts
$budgetService->addBudgetLine(
    budget: $budget,
    account: $salariesAccount,
    monthlyAmounts: [50000, 50000, 50000, 50000, 50000, 50000,
                     50000, 50000, 50000, 50000, 50000, 50000],
    costCenter: $hrDepartment,
    notes: 'Monthly salaries for HR department'
);

// Distribute annual amount evenly
$monthlyAmounts = $budgetService->distributeEvenly(120000);
// Returns: [10000, 10000, 10000, ...]

// Distribute based on seasonal pattern
$pattern = [8, 8, 10, 10, 12, 12, 10, 10, 8, 6, 3, 3]; // Percentages
$monthlyAmounts = $budgetService->distributeByPattern(120000, $pattern);
// Higher in summer months, lower in winter

// Approve and activate
$budget->approve();
$budget->activate();

// Get variance report
$report = $budgetService->getBudgetVsActualReport($budget);

foreach ($report['lines'] as $line) {
    echo "Account: " . $line['account']->name;
    echo "Budgeted: " . $line['annual']['budgeted'];
    echo "Actual: " . $line['annual']['actual'];
    echo "Variance: " . $line['annual']['variance'];
    echo "Variance %: " . $line['annual']['variance_percent'] . "%";
}

// Get over-budget alerts (>10% over)
$alerts = $budgetService->getVarianceAlerts($budget, threshold: 10);

foreach ($alerts as $alert) {
    echo "Warning: {$alert['account']->name} is {$alert['variance_percent']}% over budget";
}

// Create budget revision for mid-year changes
$revision = $budgetService->createRevision(
    $budget,
    'Mid-year adjustment for increased marketing',
    [
        $lineId => ['july' => 8000, 'august' => 8000, 'september' => 8000]
    ]
);

// Copy budget to next year with 5% increase
$newBudget = $budgetService->copyBudget(
    $lastYearBudget,
    $newFiscalPeriod,
    adjustmentPercent: 5
);
```

### Best Practices

| Do | Don't |
|----|-------|
| Create budget before fiscal year starts | Create budget mid-year |
| Review variance monthly | Wait until year-end |
| Use cost centers for departments | Put everything in one bucket |
| Create revisions for changes >10% | Ignore budget after approval |
| Document assumptions in notes | Leave budget lines unexplained |

---

## Feature 4: Cost Center Accounting

### What Is It?

Cost centers track income and expenses by department, project, location, or any organizational unit. See profitability and spending for each part of your business separately.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Departmental Accountability** | Each department tracks its costs |
| **Project Tracking** | Know if projects are profitable |
| **Decision Making** | Data for resource allocation |
| **Budgeting** | Budget by cost center |
| **Performance** | Compare department efficiency |

### Cost Center Types

| Type | Purpose | Example |
|------|---------|---------|
| **Department** | Organizational unit | Sales, HR, IT |
| **Project** | Specific projects | ERP Implementation |
| **Location** | Geographic | Erbil Office, Baghdad Office |
| **Product Line** | Product groups | Electronics, Furniture |

### When to Use

| Scenario | Use Cost Centers |
|----------|-----------------|
| Multiple departments | Track each department's costs |
| Project-based work | Track project profitability |
| Multiple locations | Compare location performance |
| Budget allocation | Assign budgets by department |
| Management reporting | Report by responsibility |

### Use Case: Departmental Cost Tracking

**Scenario:** Track costs for Sales, HR, and IT departments

**Setup:**
1. Create cost centers: CC-SALES, CC-HR, CC-IT
2. Assign managers to each
3. When recording expenses, select cost center
4. Run reports by cost center

**Result:**
```
Department   Budget    Actual    Variance
Sales       100,000    95,000    -5,000 (under)
HR           50,000    52,000    +2,000 (over)
IT           75,000    70,000    -5,000 (under)
```

### How to Use (UI Steps)

#### Creating a Cost Center

1. Navigate to **Accounting > Cost Centers**
2. Click **Create Cost Center**
3. Enter:
   - **Code** (e.g., CC-SALES)
   - **Name** (in all languages)
   - **Type** (Department, Project, etc.)
   - **Manager** (optional)
   - **Parent** (for hierarchy)
4. Click **Create**

#### Assigning Costs

When creating transactions:
1. On journal entries, select cost center per line
2. On expenses, select cost center
3. On budget lines, select cost center

#### Viewing Cost Center Reports

1. Go to **Reports > Cost Center Analysis**
2. Select cost center and date range
3. View:
   - Total expenses
   - Total revenue (if applicable)
   - Profit/Loss
   - Budget comparison

### How to Use (Code)

```php
use App\Models\CostCenter;
use App\Models\JournalEntryLine;
use App\Models\Expense;

// Create a department cost center
$salesDept = CostCenter::create([
    'code' => 'CC-SALES',
    'name' => 'Sales Department',
    'name_ar' => 'قسم المبيعات',
    'name_ckb' => 'بەشی فرۆشتن',
    'type' => CostCenter::TYPE_DEPARTMENT,
    'manager_id' => $salesManager->id,
    'is_active' => true,
]);

// Create a project cost center
$project = CostCenter::create([
    'code' => 'CC-PROJ-001',
    'name' => 'ERP Implementation Project',
    'type' => CostCenter::TYPE_PROJECT,
    'parent_id' => $itDept->id,
    'is_active' => true,
]);

// Cost center types
CostCenter::TYPE_DEPARTMENT;   // Organizational unit
CostCenter::TYPE_PROJECT;      // Project-based
CostCenter::TYPE_LOCATION;     // Geographic location
CostCenter::TYPE_PRODUCT_LINE; // Product line

// Assign cost center to journal entry line
JournalEntryLine::create([
    'journal_entry_id' => $entry->id,
    'account_id' => $expenseAccount->id,
    'cost_center_id' => $salesDept->id,
    'debit' => 1000,
    'credit' => 0,
]);

// Assign cost center to expense
Expense::create([
    'expense_type_id' => $type->id,
    'cost_center_id' => $salesDept->id,
    'amount' => 500,
    'description' => 'Office supplies for sales team',
]);

// Get financial data for cost center
$startDate = '2026-01-01';
$endDate = '2026-12-31';

$expenses = $costCenter->getTotalExpensesForPeriod($startDate, $endDate);
$revenue = $costCenter->getTotalRevenueForPeriod($startDate, $endDate);
$profitLoss = $costCenter->getProfitLossForPeriod($startDate, $endDate);

// Get budget summary for cost center
$summary = $budgetService->getCostCenterBudgetSummary($costCenter, $fiscalPeriod);
echo "Budgeted: " . $summary['total_budgeted'];
echo "Actual: " . $summary['total_actual'];
echo "Variance: " . $summary['total_variance'];
```

### Best Practices

| Do | Don't |
|----|-------|
| Use consistent code scheme (CC-XXX) | Use random codes |
| Assign managers for accountability | Leave unassigned |
| Track all significant expenses | Only track some |
| Review reports monthly | Ignore cost center reports |
| Align with organizational structure | Create arbitrary groupings |

---

## Feature 5: Cash Flow Statement

### What Is It?

The cash flow statement shows where cash came from and where it went during a period. It's organized into operating, investing, and financing activities - essential for understanding your business's cash position.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Cash Visibility** | Know where cash goes |
| **Liquidity Planning** | Plan for cash needs |
| **Investor Reports** | Standard financial statement |
| **Problem Detection** | Identify cash drains |
| **Decision Support** | Support capital decisions |

### Cash Flow Sections

| Section | What It Shows | Examples |
|---------|---------------|----------|
| **Operating** | Cash from business operations | Sales, expenses, working capital |
| **Investing** | Cash for long-term assets | Equipment purchases, asset sales |
| **Financing** | Cash from financing | Loans, owner contributions |

### When to Use

| Scenario | Run Cash Flow Report |
|----------|---------------------|
| Monthly management review | Yes |
| Bank loan application | Yes |
| Investor presentation | Yes |
| Cash planning | Yes |
| Year-end audit | Yes |

### Use Case: Monthly Cash Flow Review

**Scenario:** Management wants to understand January cash position

**Report Output:**
```
Cash Flow Statement - January 2026

OPERATING ACTIVITIES
  Net Income                            15,000
  Add: Depreciation                      2,000
  Less: Increase in Receivables         (5,000)
  Add: Increase in Payables              3,000
  Net from Operating                    15,000

INVESTING ACTIVITIES
  Purchase of Equipment                (10,000)
  Net from Investing                   (10,000)

FINANCING ACTIVITIES
  Owner Capital Contribution             5,000
  Net from Financing                     5,000

SUMMARY
  Opening Cash                          50,000
  Net Cash Change                       10,000
  Closing Cash                          60,000
```

### How to Use (UI Steps)

1. Navigate to **Reports > Financial > Cash Flow Statement**
2. Select date range
3. Click **Generate**
4. View report with:
   - Operating activities
   - Investing activities
   - Financing activities
   - Summary
5. Export to PDF or Excel

### How to Use (Code)

```php
use App\Services\Reports\CashFlowReport;

$report = new CashFlowReport('2026-01-01', '2026-12-31');
$cashFlow = $report->generate();

// Operating Activities (indirect method)
echo "Operating Activities:";
foreach ($cashFlow['operating_activities']['items'] as $item) {
    echo "  {$item['name']}: {$item['amount']}";
}
echo "Net from Operating: " . $cashFlow['operating_activities']['total'];

// Investing Activities
echo "Investing Activities:";
foreach ($cashFlow['investing_activities']['items'] as $item) {
    echo "  {$item['name']}: {$item['amount']}";
}
echo "Net from Investing: " . $cashFlow['investing_activities']['total'];

// Financing Activities
echo "Financing Activities:";
foreach ($cashFlow['financing_activities']['items'] as $item) {
    echo "  {$item['name']}: {$item['amount']}";
}
echo "Net from Financing: " . $cashFlow['financing_activities']['total'];

// Summary
echo "Opening Cash: " . $cashFlow['summary']['opening_cash'];
echo "Net Cash Change: " . $cashFlow['summary']['net_cash_change'];
echo "Closing Cash: " . $cashFlow['summary']['closing_cash'];
```

### Best Practices

| Do | Don't |
|----|-------|
| Run monthly with income statement | Only run annually |
| Compare with budget projections | Ignore variances |
| Investigate negative operating cash | Assume it's normal |
| Use for cash planning | Rely only on bank balance |

---

## Technical Reference

### Database Schema

#### bank_accounts

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| account_id | bigint | GL account FK |
| bank_name | varchar | English bank name |
| bank_name_ar | varchar | Arabic bank name |
| bank_name_ckb | varchar | Kurdish bank name |
| account_number | varchar | Bank account number |
| iban | varchar | IBAN number |
| swift_code | varchar | SWIFT/BIC code |
| branch | varchar | Branch name |
| currency_id | bigint | Currency FK |
| opening_balance | decimal | Opening balance |
| current_balance | decimal | Current balance |
| last_reconciled_date | date | Last reconciliation date |
| is_active | boolean | Active status |

#### bank_transactions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| bank_account_id | bigint | Bank account FK |
| journal_entry_id | bigint | Journal entry FK |
| type | enum | deposit/withdrawal/transfer/fee/interest |
| reference | varchar | Transaction reference |
| description | varchar | Description |
| amount | decimal | Transaction amount |
| balance_after | decimal | Balance after transaction |
| transaction_date | date | Transaction date |
| status | enum | pending/cleared/reconciled/void |
| payee | varchar | Payee name |
| check_number | varchar | Check number |

#### bank_reconciliations

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| bank_account_id | bigint | Bank account FK |
| reconciliation_number | varchar | Unique number |
| statement_date | date | Statement date |
| statement_balance | decimal | Bank statement balance |
| book_balance | decimal | Book balance |
| adjusted_book_balance | decimal | Adjusted balance |
| difference | decimal | Reconciliation difference |
| status | enum | draft/in_progress/completed/cancelled |
| completed_at | timestamp | Completion time |

#### budgets

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | English name |
| name_ar | varchar | Arabic name |
| name_ckb | varchar | Kurdish name |
| fiscal_period_id | bigint | Fiscal period FK |
| budget_type | enum | operating/capital/cash_flow/project |
| status | enum | draft/submitted/approved/active/closed |
| total_amount | decimal | Total budget amount |
| approved_at | timestamp | Approval time |

#### cost_centers

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| parent_id | bigint | Parent cost center FK |
| code | varchar | Unique code |
| name | varchar | English name |
| name_ar | varchar | Arabic name |
| name_ckb | varchar | Kurdish name |
| type | enum | department/project/location/product_line |
| manager_id | bigint | Manager user FK |
| is_active | boolean | Active status |

---

## Troubleshooting

### Bank reconciliation doesn't balance

**Problem:** After adding all items, difference is not zero.

**Solutions:**
1. Verify statement balance entered correctly
2. Check for missing transactions (deposits/withdrawals)
3. Look for bank fees not recorded
4. Check for timing differences (month boundary)
5. Review outstanding checks list

---

### Budget variance shows incorrectly

**Problem:** Budget vs actual numbers don't match expectations.

**Solutions:**
1. Verify transactions have correct posting dates
2. Check cost center assignments
3. Ensure GL accounts match budget lines
4. Review journal entry postings

---

### Cash flow doesn't match bank balance change

**Problem:** Cash flow statement closing balance doesn't match bank.

**Solutions:**
1. Check all cash accounts are included
2. Verify non-cash transactions are adjusted
3. Review GL account classifications
4. Check for unposted entries

---

*Last updated: January 2026*
