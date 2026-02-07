# GoPOS User Guide

Welcome to **GoPOS** - a comprehensive Point of Sale and Business Management System designed for retail businesses in Iraq and Kurdistan. This guide will help you understand and use all features of the system effectively.

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Point of Sale (POS)](#point-of-sale-pos)
4. [Sales Management](#sales-management)
5. [Purchase Management](#purchase-management)
6. [Inventory Management](#inventory-management)
7. [Accounting](#accounting)
8. [Human Resources (HR)](#human-resources-hr)
9. [Reports](#reports)
10. [System Settings](#system-settings)
11. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Logging In

1. Open your web browser and navigate to your GoPOS URL
2. Enter your **email address** and **password**
3. Click **Login**

### First-Time Setup

After logging in for the first time, we recommend:

1. **Update your profile** - Click on your name in the top-right corner
2. **Set your language** - Choose between English, Arabic, or Kurdish
3. **Familiarize yourself with the sidebar** - Navigate through different modules

### Understanding Your Role

Your access to features depends on your assigned role:

| Role | Access Level |
|------|--------------|
| **Super Admin** | Full access to all features |
| **Manager** | POS, Sales, Inventory, Reports |
| **Accountant** | Sales, Purchases, Full Accounting, Payroll |
| **Cashier** | POS operations only |
| **Warehouse Staff** | Inventory management |
| **HR Manager** | Full HR access |
| **HR Staff** | Attendance and leave management |

---

## Dashboard Overview

The dashboard provides a quick overview of your business:

- **Today's Sales** - Total sales for the current day
- **Monthly Revenue** - Revenue for the current month
- **Low Stock Alerts** - Products that need reordering
- **Recent Transactions** - Latest sales and purchases
- **Best Selling Products** - Top performing products
- **Quick Actions** - Shortcuts to common tasks

---

## Point of Sale (POS)

The POS module is designed for fast and efficient retail transactions.

### Starting a Shift

Before making sales, you must open a shift:

1. Go to **POS** > **Shift Management**
2. Click **Open Shift**
3. Enter the **Opening Cash Amount** in your drawer
4. Click **Confirm**

### Making a Sale

1. Navigate to **POS** from the main menu
2. **Add Products:**
   - Scan the barcode, OR
   - Search by product name, OR
   - Click on product from the grid
3. **Adjust Quantities:** Use +/- buttons or enter directly
4. **Select Customer** (optional): Search and select a customer
5. **Apply Discounts** (if applicable)
6. Click **Checkout**

### Payment Processing

GoPOS supports multiple payment methods:

#### Single Payment
1. Select payment method (Cash, Card, Bank Transfer, Mobile)
2. Enter amount received
3. System calculates change automatically
4. Click **Complete Sale**

#### Split Payment
For customers paying with multiple methods:

1. Click **Split Payment**
2. Enter amount for first method
3. Click **Add Payment**
4. Select second method and enter amount
5. Repeat until full amount is covered
6. Click **Complete Sale**

### Hold and Recall Sales

**To hold a sale:**
1. During checkout, click **Hold Sale**
2. Enter a reference note (e.g., customer name)
3. Sale is saved for 24 hours

**To recall a held sale:**
1. Click **Held Sales** button
2. Select the sale to recall
3. Continue with checkout

### Customer Loyalty Program

If loyalty is enabled, customers earn points on purchases:

- Points are awarded automatically based on purchase amount
- View customer's points balance during checkout
- Redeem points for discounts

### Closing a Shift

At the end of your shift:

1. Go to **POS** > **Shift Management**
2. Click **Close Shift**
3. Count your cash drawer
4. Enter the **Counted Amount**
5. Review the shift summary
6. Click **Confirm Close**

### Viewing Shift Reports (Z-Report)

1. Go to **POS** > **Shift Report**
2. Select the shift to view
3. View:
   - Total sales
   - Payment method breakdown
   - Cash movements (in/out)
   - Variance (expected vs counted)
4. Click **Print** to generate a report

---

## Sales Management

### Creating a Sale (Back Office)

For sales not processed through POS:

1. Go to **Sales** > **Sales**
2. Click **Create Sale**
3. Select **Customer** (or leave empty for walk-in)
4. Add products:
   - Search and select product
   - Enter quantity
   - Adjust price if needed
5. Add any **discounts**
6. Click **Save**

### Viewing Sales

1. Go to **Sales** > **Sales**
2. Use filters to find specific sales:
   - Date range
   - Customer
   - Status
   - Payment status
3. Click on a sale to view details

### Printing Invoices

1. Open the sale record
2. Click **Print Invoice**
3. Choose print or download option

### Processing Returns

When a customer returns items:

1. Go to **Sales** > **Sale Returns**
2. Click **Create Return**
3. Select the **Original Sale**
4. Select items being returned
5. Enter return reason
6. Choose refund method
7. Click **Save**

### Managing Customers

#### Adding a Customer
1. Go to **Sales** > **Customers**
2. Click **Create Customer**
3. Fill in details:
   - Name (required)
   - Phone number
   - Email
   - Address
   - Tax number (if applicable)
4. Click **Save**

#### Viewing Customer Statement
1. Open customer record
2. Click **Statement**
3. View all transactions and balance
4. Click **Print** to generate statement

---

## Purchase Management

### Creating a Purchase Order

1. Go to **Purchases** > **Purchases**
2. Click **Create Purchase**
3. Select **Supplier**
4. Add products:
   - Search and select product
   - Enter quantity
   - Enter cost price
5. Enter **Invoice Number** from supplier
6. Click **Save**

### Receiving Inventory

When purchase is saved, inventory is automatically updated based on purchase status.

### Processing Purchase Returns

For returning items to suppliers:

1. Go to **Purchases** > **Purchase Returns**
2. Click **Create Return**
3. Select the **Original Purchase**
4. Select items being returned
5. Enter reason
6. Click **Save**

### Managing Suppliers

1. Go to **Purchases** > **Suppliers**
2. Click **Create Supplier**
3. Fill in:
   - Company name
   - Contact person
   - Phone/Email
   - Address
   - Payment terms
4. Click **Save**

---

## Inventory Management

### Product Management

#### Creating a Product

1. Go to **Inventory** > **Products**
2. Click **Create Product**
3. Fill in **Basic Information:**
   - Name (required)
   - SKU/Code
   - Barcode
   - Category
   - Unit of measure
4. Set **Pricing:**
   - Cost price
   - Selling price
5. Configure **Inventory:**
   - Track inventory (yes/no)
   - Low stock alert level
   - Enable batch tracking (for expiry dates)
   - Enable serial tracking (for individual units)
6. Click **Save**

#### Managing Categories

1. Go to **Inventory** > **Categories**
2. Click **Create Category**
3. Enter name and optional parent category
4. Click **Save**

#### Managing Units

1. Go to **Inventory** > **Units**
2. Click **Create Unit**
3. Enter unit name (e.g., Piece, Box, Kg)
4. Click **Save**

### Multi-Warehouse Management

#### Setting Up Warehouses

1. Go to **Inventory** > **Warehouses**
2. Click **Create Warehouse**
3. Enter:
   - Warehouse name
   - Address
   - Contact person
   - Is active
4. Click **Save**

#### Viewing Stock by Warehouse

1. Go to **Inventory** > **Products**
2. Click on a product
3. View the **Warehouse Stock** section

### Stock Transfers

For moving inventory between warehouses:

1. Go to **Inventory** > **Stock Transfers**
2. Click **Create Transfer**
3. Select:
   - Source warehouse
   - Destination warehouse
4. Add products and quantities
5. Click **Save** (creates draft)
6. Click **Submit** for approval
7. Approver reviews and clicks **Approve**
8. Click **Complete** when items are received

### Stock Counts (Physical Inventory)

For verifying actual inventory:

1. Go to **Inventory** > **Stock Counts**
2. Click **Create Stock Count**
3. Select:
   - Warehouse
   - Count type (Full, Partial, Cycle)
4. If partial, select products to count
5. Click **Save**
6. **Perform count:**
   - Enter counted quantities for each product
   - System shows variance (counted vs expected)
7. Click **Post** to apply adjustments

### Batch and Serial Tracking

#### Batch Tracking
For products with expiry dates:

- Enable "Track Batches" on product
- When receiving inventory, enter:
  - Batch number
  - Manufacturing date
  - Expiry date
- System uses FIFO (First In, First Out) by default

#### Serial Tracking
For items with unique serial numbers:

- Enable "Track Serials" on product
- When receiving, enter individual serial numbers
- When selling, select specific serial
- Track warranty start/end dates

### Inventory Movements

View all inventory transactions:

1. Go to **Inventory** > **Inventory Movements**
2. Filter by:
   - Product
   - Movement type (Purchase, Sale, Adjustment, Transfer)
   - Date range
   - Warehouse

---

## Accounting

### Chart of Accounts

View and manage your accounts:

1. Go to **Accounting** > **Accounts**
2. View accounts organized by type:
   - Assets
   - Liabilities
   - Equity
   - Revenue
   - Expenses
3. To create account:
   - Click **Create Account**
   - Enter account code and name
   - Select account type
   - Select parent account (for sub-accounts)
   - Click **Save**

### Journal Entries

For manual accounting entries:

1. Go to **Accounting** > **Journal Entries**
2. Click **Create Entry**
3. Enter:
   - Entry date
   - Reference number
   - Description
4. Add lines:
   - Select account
   - Enter debit OR credit amount
5. **Total debits must equal total credits**
6. Click **Save**

### Managing Expenses

1. Go to **Accounting** > **Expenses**
2. Click **Create Expense**
3. Fill in:
   - Date
   - Expense type
   - Amount
   - Description
   - Reference
4. Click **Save**

### Managing Income

1. Go to **Accounting** > **Incomes**
2. Click **Create Income**
3. Fill in:
   - Date
   - Income type
   - Amount
   - Description
4. Click **Save**

### Tax Management

#### Setting Up Tax Codes
1. Go to **Accounting** > **Tax Codes**
2. Click **Create Tax Code**
3. Enter:
   - Name (e.g., "Standard VAT")
   - Rate (e.g., 15%)
   - Tax type
4. Click **Save**

### Currency Management

1. Go to **Accounting** > **Currencies**
2. Default currencies: IQD (Iraqi Dinar), USD
3. To add/edit:
   - Click **Create Currency**
   - Enter code, name, symbol
   - Set exchange rate
   - Click **Save**

### Audit Logs

View system activity:

1. Go to **Accounting** > **Audit Logs**
2. Filter by:
   - User
   - Action type
   - Date range
3. View details of each action

---

## Human Resources (HR)

### Employee Management

#### Adding an Employee

1. Go to **HR** > **Employees**
2. Click **Create Employee**
3. Fill in **Personal Information:**
   - Full name
   - Employee ID
   - Date of birth
   - Gender
   - National ID
   - Contact details
4. Fill in **Employment Details:**
   - Department
   - Position
   - Join date
   - Employment type (Full-time, Part-time, Contract)
   - Work schedule
5. Fill in **Compensation:**
   - Basic salary
   - Bank account details
6. Click **Save**

#### Managing Documents

1. Open employee record
2. Go to **Documents** tab
3. Click **Add Document**
4. Upload file (contract, ID copy, etc.)
5. Enter document type and expiry date
6. Click **Save**

### Department and Position Setup

#### Creating Departments
1. Go to **HR** > **Departments**
2. Click **Create Department**
3. Enter name and optional parent department
4. Click **Save**

#### Creating Positions
1. Go to **HR** > **Positions**
2. Click **Create Position**
3. Enter:
   - Title
   - Department
   - Salary range
4. Click **Save**

### Attendance Management

#### Recording Attendance

**Automatic (if using time clock):**
- Employees clock in/out using the system
- Late arrival is automatically flagged

**Manual Entry:**
1. Go to **HR** > **Attendances**
2. Click **Create Attendance**
3. Select employee
4. Enter date and times
5. Click **Save**

#### Viewing Attendance

1. Go to **HR** > **Attendances**
2. Filter by:
   - Employee
   - Department
   - Date range
3. View status (Present, Late, Absent, On Leave)

### Work Schedule Setup

1. Go to **HR** > **Work Schedules**
2. Click **Create Schedule**
3. Define:
   - Schedule name
   - Working days
   - Start/End times
   - Break times
4. Click **Save**
5. Assign to employees in their profile

### Holiday Calendar

1. Go to **HR** > **Holidays**
2. Click **Create Holiday**
3. Enter:
   - Holiday name
   - Date
   - Type (Public, Religious, Company)
4. Click **Save**

### Leave Management

#### Setting Up Leave Types

1. Go to **HR** > **Leave Types**
2. Click **Create Leave Type**
3. Enter:
   - Name (e.g., Annual Leave, Sick Leave)
   - Annual allowance
   - Carry forward rules
   - Paid/Unpaid
4. Click **Save**

#### Requesting Leave (as Employee)

1. Go to **HR** > **Leave Requests**
2. Click **Create Request**
3. Select:
   - Leave type
   - Start date
   - End date
   - Reason
4. Click **Submit**

#### Approving Leave (as Manager/HR)

1. Go to **HR** > **Leave Requests**
2. Find pending requests
3. Click on request to review
4. Click **Approve** or **Reject**
5. Add comments if needed

### Payroll Processing

#### Setting Up Payroll Components

1. Go to **HR** > **Payroll Components**
2. Click **Create Component**
3. Define:
   - Name (e.g., Basic Salary, Transportation Allowance)
   - Type (Earning or Deduction)
   - Calculation method (Fixed, Percentage, Formula)
4. Click **Save**

#### Assigning Components to Employees

1. Open employee record
2. Go to **Payroll Components** tab
3. Add applicable components with values

#### Processing Monthly Payroll

1. Go to **HR** > **Payroll Periods**
2. Click **Create Period**
3. Select month and year
4. Click **Generate Payslips**
5. Review generated payslips
6. Make any adjustments
7. Click **Approve Payroll**
8. Click **Process Payment**

#### Viewing Payslips

1. Go to **HR** > **Payroll Periods**
2. Open the period
3. Click on individual payslip to view
4. Click **Print** to generate PDF

### Employee Loans

#### Creating a Loan

1. Go to **HR** > **Employee Loans**
2. Click **Create Loan**
3. Enter:
   - Employee
   - Loan amount
   - Number of installments
   - Start date
4. Click **Save**
5. Approve the loan

#### Loan Deductions

- Approved loans are automatically deducted from monthly payroll
- Track repayment progress in the loan record

---

## Reports

Access reports from **Reports** in the main menu.

### Financial Reports

#### Balance Sheet
Shows assets, liabilities, and equity at a point in time:
1. Go to **Reports** > **Balance Sheet**
2. Select date
3. Click **Generate**

#### Income Statement (Profit & Loss)
Shows revenue and expenses for a period:
1. Go to **Reports** > **Income Statement**
2. Select date range
3. Click **Generate**

#### Trial Balance
Lists all account balances:
1. Go to **Reports** > **Trial Balance**
2. Select date
3. Click **Generate**

#### Cash Flow Statement
Shows cash movements by activity:
1. Go to **Reports** > **Cash Flow**
2. Select date range
3. Click **Generate**

### Sales Reports

#### Sales Report
Summary of all sales:
1. Go to **Reports** > **Sales Report**
2. Select date range
3. Filter by customer, product, or category
4. Click **Generate**

#### Sales by Product
Performance by product:
1. Go to **Reports** > **Sale by Product**
2. Select date range
3. View quantity sold, revenue, and profit

#### Top Customers
Customer ranking by purchases:
1. Go to **Reports** > **Top Customers**
2. Select date range
3. View customer purchase totals

#### Customer Balances
Accounts receivable aging:
1. Go to **Reports** > **Customer Balances**
2. View outstanding amounts by customer

### Inventory Reports

#### Stock Movement Report
All inventory transactions:
1. Go to **Reports** > **Stock Movement**
2. Select date range and warehouse
3. Filter by product if needed

#### Inventory Valuation
Current inventory value:
1. Go to **Reports** > **Inventory Valuation**
2. Select valuation method
3. View total inventory value

### Purchase Reports

#### Purchases Report
Summary of all purchases:
1. Go to **Reports** > **Purchases Report**
2. Select date range
3. Filter by supplier if needed

### Exporting Reports

All reports support export:
1. Generate the report
2. Click **Export**
3. Select format (PDF, Excel, CSV)
4. File downloads automatically

---

## System Settings

Access settings from **Settings** in the main menu.

### User Management

#### Creating Users

1. Go to **Settings** > **Users**
2. Click **Create User**
3. Enter:
   - Name
   - Email
   - Password
   - Role
4. Click **Save**

#### Editing Users

1. Go to **Settings** > **Users**
2. Click on user to edit
3. Update information
4. Click **Save**

### Role Management

#### Viewing Roles

1. Go to **Settings** > **Roles**
2. View available roles and their permissions

#### Creating Custom Roles

1. Go to **Settings** > **Roles**
2. Click **Create Role**
3. Enter role name
4. Select permissions for each module
5. Click **Save**

### Permission Management

1. Go to **Settings** > **Permissions**
2. View all available permissions
3. Permissions are organized by module:
   - POS
   - Sales
   - Purchases
   - Inventory
   - Accounting
   - HR
   - Reports
   - Settings

---

## Troubleshooting

### Common Issues

#### Cannot Login
- Verify email and password are correct
- Check if your account is active
- Contact administrator if locked out

#### POS Not Working
- Ensure you have an open shift
- Check your role has POS access
- Refresh the page

#### Products Not Showing
- Check product is marked as "Active"
- Verify product has stock (if tracking inventory)
- Check your warehouse selection

#### Payment Methods Missing
- Contact administrator to enable payment methods
- Check system settings

#### Reports Show No Data
- Verify date range selection
- Check if transactions exist for selected period
- Confirm you have permission to view reports

#### Inventory Not Updating
- Check if product has "Track Inventory" enabled
- Verify warehouse is correct
- Review inventory movements for the product

### Getting Help

If you encounter issues not covered here:

1. **Check the documentation** - Review relevant sections
2. **Contact your system administrator** - For account and permission issues
3. **Report bugs** - Note the steps to reproduce the issue

---

## Keyboard Shortcuts

### POS Shortcuts
| Shortcut | Action |
|----------|--------|
| `F1` | Search products |
| `F2` | Select customer |
| `F3` | Apply discount |
| `F4` | Checkout |
| `Esc` | Cancel current action |

### General Shortcuts
| Shortcut | Action |
|----------|--------|
| `Ctrl + S` | Save current form |
| `Ctrl + N` | Create new record |
| `Ctrl + F` | Search |

---

## Glossary

| Term | Definition |
|------|------------|
| **POS** | Point of Sale - the checkout interface |
| **SKU** | Stock Keeping Unit - unique product identifier |
| **FIFO** | First In, First Out - inventory costing method |
| **GL** | General Ledger - main accounting record |
| **Z-Report** | End of day/shift sales summary |
| **Variance** | Difference between expected and actual values |
| **Batch** | Group of products with same production/expiry |
| **Serial** | Unique identifier for individual item |

---

## Version Information

- **System:** GoPOS
- **Documentation Version:** 1.0
- **Last Updated:** January 2026

---

*For technical documentation and development guides, see the `docs/` folder in the system directory.*
