# GoPOS - Product Requirements Document (PRD)

**Version:** 1.0
**Last Updated:** January 2026
**Status:** Production

---

## Executive Summary

GoPOS is an enterprise-grade Point of Sale and Business Management System built for retail, wholesale, and service businesses. The platform provides comprehensive solutions for sales management, inventory control, human resources, accounting, and business analytics with full multi-language (English, Arabic, Kurdish) and multi-currency support.

---

## Table of Contents

1. [Product Overview](#1-product-overview)
2. [System Architecture](#2-system-architecture)
3. [Core Modules](#3-core-modules)
4. [Point of Sale (POS)](#4-point-of-sale-pos)
5. [Sales Management](#5-sales-management)
6. [Inventory Management](#6-inventory-management)
7. [Purchase Management](#7-purchase-management)
8. [Human Resources](#8-human-resources)
9. [Accounting & Finance](#9-accounting--finance)
10. [Reporting & Analytics](#10-reporting--analytics)
11. [System Configuration](#11-system-configuration)
12. [Technical Specifications](#12-technical-specifications)

---

## 1. Product Overview

### 1.1 Vision

To provide businesses with an all-in-one management solution that streamlines operations, improves efficiency, and enables data-driven decision making.

### 1.2 Target Users

| User Type | Primary Use Cases |
|-----------|------------------|
| **Cashiers** | POS transactions, payment processing |
| **Store Managers** | Inventory, staff scheduling, daily operations |
| **Warehouse Staff** | Stock management, transfers, counting |
| **HR Managers** | Employee management, payroll, attendance |
| **Accountants** | Financial records, reconciliation, reporting |
| **Business Owners** | Analytics, strategic decisions, oversight |

### 1.3 Key Differentiators

- Full RTL support for Arabic and Kurdish languages
- Multi-currency with real-time exchange rates
- Comprehensive payroll with regional compliance
- Batch and serial number tracking
- Double-entry accounting integration
- Offline POS capability

---

## 2. System Architecture

### 2.1 Module Structure

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              GoPOS Platform                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐                │
│   │     POS       │   │    Sales      │   │   Purchases   │                │
│   │   System      │   │   Module      │   │    Module     │                │
│   └───────────────┘   └───────────────┘   └───────────────┘                │
│                                                                             │
│   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐                │
│   │   Inventory   │   │    Human      │   │  Accounting   │                │
│   │   Module      │   │   Resources   │   │    Module     │                │
│   └───────────────┘   └───────────────┘   └───────────────┘                │
│                                                                             │
│   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐                │
│   │   Reports     │   │   Settings    │   │   Dashboard   │                │
│   │   Module      │   │    Module     │   │    Module     │                │
│   └───────────────┘   └───────────────┘   └───────────────┘                │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Data Model Summary

| Category | Models | Description |
|----------|--------|-------------|
| Sales & POS | 7 | Sale, SaleItem, SaleReturn, Payments, HeldSale |
| Inventory | 11 | Product, Warehouse, StockTransfer, Movements |
| Purchases | 4 | Purchase, PurchaseItem, PurchaseReturn, Supplier |
| HR & Payroll | 19 | Employee, Attendance, Payslip, Leave, Loans |
| Accounting | 18 | Account, JournalEntry, Budget, BankAccount |
| Configuration | 8 | Currency, TaxCode, Category, Settings |

**Total Data Models:** 77

---

## 3. Core Modules

### 3.1 Module Overview

| Module | Purpose | Key Features |
|--------|---------|--------------|
| **POS** | Point of sale operations | Fast checkout, split payments, held sales |
| **Sales** | Customer transactions | Invoicing, returns, customer management |
| **Inventory** | Stock management | Multi-warehouse, batch tracking, transfers |
| **Purchases** | Supplier transactions | Purchase orders, receiving, returns |
| **HR** | Employee management | Payroll, attendance, leave management |
| **Accounting** | Financial management | Chart of accounts, journal entries, budgets |
| **Reports** | Business intelligence | Sales analysis, financial reports, dashboards |

---

## 4. Point of Sale (POS)

### 4.1 POS Interface

#### 4.1.1 Features

| Feature | Description | Priority |
|---------|-------------|----------|
| **Product Search** | Real-time search by name, SKU, barcode | Critical |
| **Quick Add** | One-click product addition to cart | Critical |
| **Cart Management** | Quantity adjustment, item removal | Critical |
| **Discount Application** | Per-item and total discounts | High |
| **Tax Calculation** | Automatic tax based on tax codes | High |
| **Customer Assignment** | Link sale to customer account | Medium |
| **Hold/Resume** | Pause current sale for later | High |

#### 4.1.2 Payment Processing

| Payment Type | Supported | Features |
|--------------|-----------|----------|
| Cash | Yes | Change calculation |
| Credit/Debit Card | Yes | Integration ready |
| Bank Transfer | Yes | Reference tracking |
| Split Payment | Yes | Multiple methods per transaction |
| Loyalty Points | Yes | Point redemption |

### 4.2 Shift Management

#### 4.2.1 Session Lifecycle

```
┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
│  Open   │ →  │ Active  │ →  │ Closing │ →  │ Closed  │
│ Session │    │ Trading │    │  Count  │    │ Session │
└─────────┘    └─────────┘    └─────────┘    └─────────┘
  Opening       Sales &        Cash           Final
  Balance      Transactions   Reconcile      Report
```

#### 4.2.2 Shift Features

| Feature | Description |
|---------|-------------|
| **Opening Cash** | Record starting cash amount |
| **Cash In/Out** | Track non-sale cash movements |
| **Transaction History** | View all shift transactions |
| **Variance Tracking** | Expected vs. actual cash |
| **Shift Report** | Detailed shift summary |

### 4.3 Receipt Printing

| Format | Description |
|--------|-------------|
| Thermal | Standard POS receipt |
| A4 Invoice | Full-page invoice |
| Email | Digital receipt delivery |

---

## 5. Sales Management

### 5.1 Customer Management

#### 5.1.1 Customer Data

| Field | Type | Required |
|-------|------|----------|
| Name | String | Yes |
| Email | String | No |
| Phone | String | No |
| Address | Text | No |
| Tax ID | String | No |
| Credit Limit | Decimal | No |
| Loyalty Program | Relation | No |

#### 5.1.2 Customer Features

| Feature | Description |
|---------|-------------|
| **Customer Statement** | Transaction history and balance |
| **Credit Management** | Credit limit enforcement |
| **Loyalty Integration** | Points earning and redemption |
| **Sales History** | Complete purchase history |
| **Balance Tracking** | Outstanding receivables |

### 5.2 Sales Transactions

#### 5.2.1 Sale Structure

```
Sale
├── Sale Number (Auto-generated: INV-0001)
├── Sale Date
├── Customer (Optional)
├── Currency + Exchange Rate
├── Tax Code
├── Sale Items[]
│   ├── Product
│   ├── Quantity
│   ├── Unit Price
│   ├── Discount
│   ├── Tax Amount
│   └── Line Total
├── Sub Total
├── Discount Amount
├── Tax Amount
├── Total Amount
├── Paid Amount
└── Payment Status (Paid/Partial/Unpaid)
```

#### 5.2.2 Sale Statuses

| Status | Description |
|--------|-------------|
| **Draft** | Not finalized |
| **Posted** | Completed sale |
| **Cancelled** | Voided transaction |

### 5.3 Sale Returns

| Feature | Description |
|---------|-------------|
| **Full Return** | Return entire sale |
| **Partial Return** | Return selected items |
| **Reason Tracking** | Document return reasons |
| **Inventory Update** | Auto-restock returned items |
| **Refund Processing** | Credit note or cash refund |

---

## 6. Inventory Management

### 6.1 Product Management

#### 6.1.1 Product Data

| Field | Type | Description |
|-------|------|-------------|
| Name | Multi-language | Product name in all languages |
| SKU | String | Stock keeping unit |
| Barcode | String | Scannable barcode |
| Category | Relation | Product category |
| Unit | Relation | Unit of measurement |
| Cost | Decimal | Purchase cost |
| Price | Decimal | Selling price |
| Tax Code | Relation | Applicable tax |

#### 6.1.2 Advanced Tracking

| Feature | Description | Use Case |
|---------|-------------|----------|
| **Batch Tracking** | Group products by batch/lot | Food, pharmaceuticals |
| **Serial Tracking** | Individual unit tracking | Electronics, warranties |
| **Expiry Dates** | Track expiration | Perishables |
| **Warranty** | Track warranty periods | Equipment |

### 6.2 Multi-Warehouse

#### 6.2.1 Warehouse Configuration

| Setting | Description |
|---------|-------------|
| **Default Warehouse** | Primary receiving location |
| **Negative Stock** | Allow/prevent overselling |
| **Manager Assignment** | Responsible person |
| **Location Tracking** | Bin/rack positions |

#### 6.2.2 Stock Visibility

```
┌─────────────────────────────────────────────────────────────┐
│                     Product: Widget A                        │
├─────────────────────────────────────────────────────────────┤
│  Warehouse          │ Available │ Reserved │ On Order      │
├─────────────────────┼───────────┼──────────┼───────────────┤
│  Main Warehouse     │    150    │    20    │     50        │
│  Store A            │     45    │     5    │      -        │
│  Store B            │     30    │     3    │      -        │
├─────────────────────┼───────────┼──────────┼───────────────┤
│  TOTAL              │    225    │    28    │     50        │
└─────────────────────────────────────────────────────────────┘
```

### 6.3 Stock Transfers

#### 6.3.1 Transfer Workflow

```
┌─────────┐    ┌─────────┐    ┌───────────┐    ┌──────────┐    ┌───────────┐
│  Draft  │ →  │ Pending │ →  │ In Transit│ →  │ Partial  │ →  │ Completed │
└─────────┘    └─────────┘    └───────────┘    └──────────┘    └───────────┘
  Create       Awaiting        Items           Some items      All items
  request      approval        shipped         received        received
```

#### 6.3.2 Transfer Features

| Feature | Description |
|---------|-------------|
| **Approval Workflow** | Manager approval required |
| **Quantity Verification** | Expected vs. received |
| **Discrepancy Notes** | Document shortages |
| **Batch Selection** | Choose specific batches |

### 6.4 Stock Counts

#### 6.4.1 Count Types

| Type | Frequency | Scope |
|------|-----------|-------|
| **Full Count** | Annual | All products |
| **Partial Count** | Quarterly | Selected categories |
| **Cycle Count** | Weekly/Monthly | Rotating selection |
| **Spot Check** | As needed | Specific products |

#### 6.4.2 Count Process

1. **Initialize** - Generate count sheet from current stock
2. **Count** - Record physical quantities
3. **Review** - Identify discrepancies
4. **Adjust** - Post variance adjustments
5. **Complete** - Finalize and lock count

### 6.5 Reorder Rules

| Parameter | Description |
|-----------|-------------|
| **Minimum Quantity** | Safety stock level |
| **Maximum Quantity** | Storage limit |
| **Reorder Point** | Trigger level |
| **Reorder Quantity** | Standard order size |
| **Lead Time** | Days to receive |
| **Preferred Supplier** | Default vendor |

### 6.6 Costing Methods

| Method | Description | Best For |
|--------|-------------|----------|
| **FIFO** | First In, First Out | Perishables |
| **LIFO** | Last In, First Out | Tax optimization |
| **AVCO** | Weighted Average | Commodities |
| **Specific** | Individual tracking | High-value items |

---

## 7. Purchase Management

### 7.1 Supplier Management

| Field | Description |
|-------|-------------|
| Name | Supplier name |
| Contact | Contact person |
| Phone/Email | Communication |
| Address | Shipping address |
| Tax ID | Tax identification |
| Payment Terms | Net 30, etc. |
| Status | Active/Inactive |

### 7.2 Purchase Orders

#### 7.2.1 PO Structure

```
Purchase Order
├── PO Number (Auto-generated)
├── Purchase Date
├── Supplier
├── Currency + Exchange Rate
├── Tax Code
├── Purchase Items[]
│   ├── Product
│   ├── Quantity
│   ├── Unit Cost
│   ├── Tax Amount
│   └── Line Total
├── Sub Total
├── Discount Amount
├── Tax Amount
├── Total Amount
├── Paid Amount
└── Payment Status
```

### 7.3 Purchase Returns

| Feature | Description |
|---------|-------------|
| **Return Request** | Initiate return to supplier |
| **Item Selection** | Choose items to return |
| **Reason Codes** | Defective, wrong item, etc. |
| **Stock Adjustment** | Reduce inventory |
| **Credit Note** | Supplier credit tracking |

---

## 8. Human Resources

### 8.1 Employee Management

#### 8.1.1 Employee Data

| Category | Fields |
|----------|--------|
| **Personal** | Name (multi-language), DOB, nationality, gender |
| **Employment** | Hire date, position, department, manager |
| **Contract** | Type, probation, end date |
| **Financial** | Bank details, salary, tax ID |
| **Emergency** | Contact name, relation, phone |

#### 8.1.2 Employment Types

| Type | Description |
|------|-------------|
| Full-time | Regular employees |
| Part-time | Reduced hours |
| Contract | Fixed-term |
| Temporary | Short-term |
| Intern | Training position |

#### 8.1.3 Employee Statuses

| Status | Description |
|--------|-------------|
| Active | Currently employed |
| On Leave | Temporary absence |
| Suspended | Disciplinary |
| Terminated | Involuntary exit |
| Resigned | Voluntary exit |

### 8.2 Attendance Management

#### 8.2.1 Attendance Tracking

| Feature | Description |
|---------|-------------|
| **Clock In/Out** | Time recording |
| **Break Tracking** | Lunch/break duration |
| **Overtime** | Extra hours tracking |
| **Late Detection** | Late arrival alerts |
| **Location Logging** | IP/location capture |

#### 8.2.2 Attendance Statuses

| Status | Description |
|--------|-------------|
| Present | Normal attendance |
| Absent | No attendance |
| Late | Arrived after start time |
| Leave | Approved absence |
| Half Day | Partial attendance |

### 8.3 Leave Management

#### 8.3.1 Leave Types

| Type | Examples |
|------|----------|
| Annual | Vacation time |
| Sick | Medical leave |
| Maternity/Paternity | Parental leave |
| Unpaid | Leave without pay |
| Compensatory | Time off in lieu |

#### 8.3.2 Leave Request Workflow

```
┌──────────┐    ┌──────────┐    ┌──────────┐
│ Pending  │ →  │ Approved │    │ Rejected │
│          │    │          │    │          │
└──────────┘    └──────────┘    └──────────┘
     │               │               │
     └───────────────┴───────────────┘
                     │
              ┌──────────┐
              │Cancelled │
              └──────────┘
```

#### 8.3.3 Leave Balance

| Component | Description |
|-----------|-------------|
| Entitled | Annual allowance |
| Carried | Previous year |
| Adjustments | Manual changes |
| Used | Taken days |
| Pending | Awaiting approval |
| Available | Remaining balance |

### 8.4 Payroll Management

#### 8.4.1 Payroll Components

| Type | Examples |
|------|----------|
| **Earnings** | Basic salary, allowances, bonuses |
| **Deductions** | Tax, insurance, loans |

#### 8.4.2 Payroll Period Workflow

```
┌─────────┐    ┌────────────┐    ┌───────────┐    ┌────────┐
│  Draft  │ →  │ Processing │ →  │ Processed │ →  │ Closed │
└─────────┘    └────────────┘    └───────────┘    └────────┘
  Created       Calculating        Payslips         Locked
  period        payroll           generated        for edits
```

#### 8.4.3 Payslip Structure

```
Payslip
├── Payslip Number (Auto-generated)
├── Employee
├── Period (Year/Month)
├── Working Days
├── Actual Days Worked
├── Earnings[]
│   ├── Basic Salary
│   ├── Overtime Pay
│   └── Allowances
├── Deductions[]
│   ├── Tax
│   ├── Insurance
│   └── Loan Repayment
├── Gross Salary
├── Net Salary
└── Status (Draft/Finalized/Submitted)
```

### 8.5 Employee Loans

| Feature | Description |
|---------|-------------|
| **Loan Creation** | Amount, terms, interest |
| **Repayment Schedule** | Monthly installments |
| **Payroll Integration** | Auto-deduction |
| **Balance Tracking** | Outstanding amount |

---

## 9. Accounting & Finance

### 9.1 Chart of Accounts

#### 9.1.1 Account Structure

| Level | Example | Description |
|-------|---------|-------------|
| Type | Assets | Main category |
| Category | Current Assets | Sub-category |
| Account | Cash | Individual account |
| Sub-account | Petty Cash | Detail account |

#### 9.1.2 Account Types

| Type | Normal Balance | Examples |
|------|----------------|----------|
| Asset | Debit | Cash, Inventory, Equipment |
| Liability | Credit | Accounts Payable, Loans |
| Equity | Credit | Capital, Retained Earnings |
| Revenue | Credit | Sales, Service Income |
| Expense | Debit | Salaries, Rent, Utilities |

### 9.2 Journal Entries

#### 9.2.1 Entry Structure

```
Journal Entry
├── Entry Number (Auto-generated)
├── Entry Date
├── Reference
├── Memo
├── Lines[]
│   ├── Account
│   ├── Debit Amount
│   ├── Credit Amount
│   └── Line Memo
└── Status (Draft/Submitted/Posted/Rejected)
```

#### 9.2.2 Posting Rules

| Rule | Description |
|------|-------------|
| **Balanced** | Total debits = Total credits |
| **Complete** | All required fields filled |
| **Approved** | Manager approval for large amounts |
| **Sequential** | Entry numbers in order |

### 9.3 Tax Management

#### 9.3.1 Tax Codes

| Field | Description |
|-------|-------------|
| Code | Unique identifier |
| Name | Tax name |
| Rate | Percentage |
| Type | Inclusive/Exclusive |
| Active | In use |

#### 9.3.2 Tax Application

| Transaction | Tax Handling |
|-------------|--------------|
| Sales | Tax on total or per line |
| Purchases | Tax credit tracking |
| Returns | Tax reversal |

### 9.4 Multi-Currency

#### 9.4.1 Currency Configuration

| Setting | Description |
|---------|-------------|
| **Base Currency** | Primary reporting currency |
| **Exchange Rate** | Conversion rate |
| **Decimal Places** | Precision setting |
| **Symbol** | Display symbol |

#### 9.4.2 Currency Operations

| Operation | Description |
|-----------|-------------|
| **Conversion** | Real-time rate application |
| **Revaluation** | Period-end adjustment |
| **Gain/Loss** | Exchange rate variance |

### 9.5 Banking

#### 9.5.1 Bank Account Management

| Feature | Description |
|---------|-------------|
| **Account Setup** | Bank, account number, IBAN |
| **Transaction Recording** | Deposits, withdrawals |
| **Statement Import** | Bank file upload |
| **Reconciliation** | Match transactions |

#### 9.5.2 Reconciliation Process

```
┌─────────┐    ┌─────────────┐    ┌───────────┐    ┌───────────┐
│  Draft  │ →  │ In Progress │ →  │ Completed │    │ Cancelled │
└─────────┘    └─────────────┘    └───────────┘    └───────────┘
  Create        Match             All matched       Abandoned
  session       transactions      and balanced
```

### 9.6 Budget Management

#### 9.6.1 Budget Types

| Type | Description |
|------|-------------|
| Operating | Day-to-day expenses |
| Capital | Asset purchases |
| Cash Flow | Cash planning |
| Project | Project-specific |

#### 9.6.2 Budget Workflow

```
┌─────────┐    ┌───────────┐    ┌──────────┐    ┌────────┐    ┌────────┐
│  Draft  │ →  │ Submitted │ →  │ Approved │ →  │ Active │ →  │ Closed │
└─────────┘    └───────────┘    └──────────┘    └────────┘    └────────┘
```

### 9.7 Income & Expenses

| Category | Tracking |
|----------|----------|
| **Income** | Non-sales revenue (rent, interest) |
| **Expenses** | Non-purchase costs (utilities, supplies) |

---

## 10. Reporting & Analytics

### 10.1 Available Reports

#### 10.1.1 Sales Reports

| Report | Description |
|--------|-------------|
| **Sales Report** | Sales by period |
| **Sales by Product** | Revenue per product |
| **Top Customers** | Customer ranking |
| **Customer Balances** | Receivables aging |

#### 10.1.2 Inventory Reports

| Report | Description |
|--------|-------------|
| **Inventory Valuation** | Stock value |
| **Stock Movement** | Transaction history |
| **Low Stock** | Below reorder point |
| **Expiring Products** | Near expiry |

#### 10.1.3 Purchase Reports

| Report | Description |
|--------|-------------|
| **Purchases Report** | Purchases by period |
| **Supplier Analysis** | Vendor performance |
| **Purchase Returns** | Return analysis |

#### 10.1.4 Financial Reports

| Report | Description |
|--------|-------------|
| **Trial Balance** | Account balances |
| **Income Statement** | Profit & Loss |
| **Balance Sheet** | Financial position |
| **Cash Flow** | Cash movement |

#### 10.1.5 HR Reports

| Report | Description |
|--------|-------------|
| **Attendance Summary** | Attendance rates |
| **Leave Report** | Leave usage |
| **Payroll Summary** | Salary totals |

### 10.2 Dashboard Widgets

| Widget | Data Shown |
|--------|------------|
| **Sales Trend** | Daily/weekly sales chart |
| **Top Products** | Best sellers |
| **Top Customers** | Highest revenue customers |
| **Low Stock Alert** | Products needing reorder |
| **Pending Leaves** | Awaiting approval |
| **Payroll Overview** | Current period summary |
| **POS Sessions** | Active sessions |
| **Cashier Performance** | Sales by cashier |

### 10.3 Export Options

| Format | Description |
|--------|-------------|
| **PDF** | Print-ready reports |
| **Excel** | Data export |
| **Screen** | Interactive view |

---

## 11. System Configuration

### 11.1 User Management

#### 11.1.1 User Roles

| Role | Typical Access |
|------|----------------|
| Administrator | Full system access |
| Manager | Reports, approvals |
| Cashier | POS only |
| Accountant | Accounting module |
| HR Manager | HR module |
| Warehouse | Inventory module |

#### 11.1.2 Permissions

| Resource | Actions |
|----------|---------|
| All Resources | Create, Read, Update, Delete |
| Sensitive Data | View, Export |
| Configuration | Modify settings |

### 11.2 Localization

#### 11.2.1 Supported Languages

| Language | Code | Direction |
|----------|------|-----------|
| English | en | LTR |
| Arabic | ar | RTL |
| Kurdish (Sorani) | ckb | RTL |

#### 11.2.2 Regional Settings

| Setting | Options |
|---------|---------|
| Date Format | DD/MM/YYYY, MM/DD/YYYY |
| Number Format | 1,000.00 or 1.000,00 |
| Currency Display | Symbol or code |

### 11.3 System Settings

| Category | Settings |
|----------|----------|
| **Company** | Name, logo, address |
| **Defaults** | Currency, warehouse, tax |
| **Sequences** | Number formats |
| **Notifications** | Email settings |

---

## 12. Technical Specifications

### 12.1 Technology Stack

| Component | Technology | Version |
|-----------|------------|---------|
| Backend | Laravel | v12 |
| Frontend | Filament | v4 |
| Real-time | Livewire | v3 |
| CSS | Tailwind CSS | v4 |
| Language | PHP | 8.4.1 |
| Testing | Pest | v3 |
| Performance | Laravel Octane | v2 |

### 12.2 Data Models

**Total Models:** 77

| Category | Count |
|----------|-------|
| Sales & POS | 7 |
| Inventory | 11 |
| Purchases | 4 |
| HR & Payroll | 19 |
| Accounting | 18 |
| Users & Auth | 3 |
| Configuration | 8 |
| Other | 7 |

### 12.3 Services

| Service | Purpose |
|---------|---------|
| POSSessionService | POS shift management |
| InventoryService | Stock operations |
| PayrollService | Payroll processing |
| GeneralLedgerService | GL operations |
| TaxCalculationService | Tax computation |
| CurrencyService | Currency conversion |
| BankReconciliationService | Bank matching |
| LoyaltyService | Loyalty program |

### 12.4 Audit & Security

| Feature | Description |
|---------|-------------|
| **Audit Trail** | Full change history |
| **Role-Based Access** | Granular permissions |
| **Session Tracking** | User activity logging |
| **Data Validation** | Input sanitization |
| **Soft Deletes** | Data recovery |

### 12.5 Performance Features

| Feature | Description |
|---------|-------------|
| **Eager Loading** | N+1 query prevention |
| **Query Scopes** | Reusable queries |
| **Caching** | Response caching |
| **Queue Jobs** | Background processing |
| **Octane** | High-performance serving |

---

## Appendix A: Entity Relationship Summary

### Core Relationships

```
Customer ─────────── Sale ─────────── SaleItem ─────────── Product
                       │                                      │
                       └── Currency                           │
                       └── TaxCode                            │
                                                              │
Supplier ─────────── Purchase ────── PurchaseItem ────────────┘
                       │
                       └── Currency
                       └── TaxCode

Employee ─────────── Attendance
    │
    ├── Department
    ├── Position
    ├── LeaveRequest ─────── LeaveType
    ├── Payslip ──────────── PayrollPeriod
    └── EmployeeLoan ─────── LoanRepayment

Account ──────────── JournalEntryLine ──────── JournalEntry
    │
    └── AccountType

Product ──────────── InventoryMovement ──────── Warehouse
    │
    ├── ProductBatch
    ├── ProductSerial
    └── ReorderRule
```

---

## Appendix B: Status Workflows

### Sale Status Flow
`Draft` → `Posted` → `Cancelled`

### Purchase Status Flow
`Draft` → `Posted` → `Cancelled`

### Transfer Status Flow
`Draft` → `Pending` → `In Transit` → `Partial/Completed`

### Leave Request Flow
`Pending` → `Approved/Rejected` → `Cancelled`

### Payroll Period Flow
`Draft` → `Processing` → `Processed` → `Closed`

### Budget Flow
`Draft` → `Submitted` → `Approved` → `Active` → `Closed`

### Bank Reconciliation Flow
`Draft` → `In Progress` → `Completed/Cancelled`

---

## Appendix C: Number Formats

| Entity | Format | Example |
|--------|--------|---------|
| Sale | INV-##### | INV-00001 |
| Purchase | PUR-##### | PUR-00001 |
| Employee | EMP-##### | EMP-00001 |
| Transfer | TRF-##### | TRF-00001 |
| Journal Entry | JE-##### | JE-00001 |

---

*Document Version: 1.0*
*Last Updated: January 2026*
*Status: Production*
