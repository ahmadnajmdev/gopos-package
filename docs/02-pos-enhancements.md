# POS Module Enhancements

## Overview

The POS (Point of Sale) module is the heart of your daily retail operations. It handles everything from simple sales to complex scenarios like split payments, customer loyalty programs, and shift management with cash tracking.

---

## Why Use These Features?

### Key Benefits

| Benefit | How It Helps Your Business |
|---------|---------------------------|
| **Cash Accountability** | Shift management tracks every IQD/USD - know exactly where money goes |
| **Flexible Payments** | Accept multiple payment methods in one transaction |
| **Customer Retention** | Loyalty programs bring customers back |
| **Workflow Efficiency** | Hold/recall sales prevents lost transactions |
| **Professional Receipts** | Multiple print formats for any printer |

### Problems It Solves

- **"Cash is always short at end of day"** - Shift tracking with variance reports
- **"Customer wants to pay half cash, half card"** - Split payment support
- **"We lose repeat customers"** - Loyalty points encourage return visits
- **"Customer forgot their wallet, lost the sale"** - Hold sale until they return
- **"Our receipts look unprofessional"** - Customizable receipt formats

---

## Who Should Read This?

| Role | Relevant Sections |
|------|-------------------|
| **Cashiers** | Shift Management, Split Payments, Hold/Recall |
| **Store Managers** | All sections - especially Shift Reports |
| **Business Owners** | Loyalty Program, Shift Reports |
| **Developers** | All sections for integration |

---

## Module Components

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          POS Enhancements                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌───────────────────┐   Track cash, prevent theft, accountability      │
│  │ Shift Management  │   USE WHEN: Starting/ending work day             │
│  └───────────────────┘                                                  │
│                                                                         │
│  ┌───────────────────┐   Multiple payment methods in one sale           │
│  │  Split Payments   │   USE WHEN: Customer pays cash + card            │
│  └───────────────────┘                                                  │
│                                                                         │
│  ┌───────────────────┐   Reward repeat customers, increase loyalty      │
│  │ Loyalty Program   │   USE WHEN: Building customer relationships      │
│  └───────────────────┘                                                  │
│                                                                         │
│  ┌───────────────────┐   Pause sales, continue later                    │
│  │  Hold/Recall      │   USE WHEN: Customer steps away temporarily      │
│  └───────────────────┘                                                  │
│                                                                         │
│  ┌───────────────────┐   Generate receipts for any printer              │
│  │ Receipt Printing  │   USE WHEN: Completing any sale                  │
│  └───────────────────┘                                                  │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Feature 1: Shift Management

### What Is It?

Shift management tracks each cashier's session from opening to closing. It records:
- Opening cash amount
- All sales and refunds during the shift
- Cash additions (petty cash deposits)
- Cash removals (change for bills)
- Closing cash count and any variances

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Cash Accountability** | Know exactly how much cash should be in the drawer |
| **Theft Prevention** | Variances are immediately visible and documented |
| **Easy Handoffs** | Clear start/end points for shift changes |
| **Audit Trail** | Complete history of all cash movements |
| **Z-Reports** | End-of-shift summary reports |

### When to Use

| Scenario | Action |
|----------|--------|
| Starting your work day | Open a new shift |
| Ending your work day | Close the shift |
| Adding petty cash to drawer | Record Cash In |
| Removing cash for change | Record Cash Out |
| Handing over to next cashier | Close shift, they open new one |
| Manager reviewing daily activity | View Z-Report |

### Use Case: Daily Retail Operations

**Scenario:** Ahmed starts his shift at 9 AM with 50,000 IQD in the drawer.

**Morning:**
1. Ahmed opens shift, enters 50,000 IQD opening cash
2. He processes sales throughout the morning
3. At noon, he records a 10,000 IQD cash-in (petty cash deposit)

**Afternoon:**
1. A customer needs 20,000 IQD broken into smaller bills
2. Ahmed records this as cash-out (removing bills)
3. He continues processing sales

**End of Shift:**
1. Ahmed counts the cash: 185,000 IQD
2. System calculated expected: 180,000 IQD
3. Ahmed is over by 5,000 IQD - notes "found loose bills under register"
4. Manager reviews the Z-report and approves

### How to Use (UI Steps)

#### Opening a Shift

1. Navigate to **Sales > Shift Management** or click the shift icon in POS
2. Click **Open Shift**
3. Enter the **Opening Cash** amount (count what's in the drawer)
4. Optionally enter a **Terminal ID** (e.g., "POS-1", "Register A")
5. Click **Open Shift**

> **Important:** You can only have one shift open at a time. Opening a new shift auto-closes any existing one.

#### During Your Shift

While working:
- All sales are automatically linked to your current shift
- Cash transactions are tracked in real-time
- View your running totals anytime in Shift Management

#### Recording Cash In/Out

For non-sale cash movements:

1. In Shift Management, click **Cash In** or **Cash Out**
2. Enter the amount
3. Add a note explaining the movement (required for audit trail)
4. Confirm the transaction

**Examples:**
- Cash In: "Petty cash deposit from manager"
- Cash Out: "Change for 50,000 bill"
- Cash Out: "Paid delivery driver"

#### Closing Your Shift

1. Navigate to **Sales > Shift Management**
2. Click **Close Shift**
3. Count your physical cash carefully
4. Enter the **Counted Cash** amount
5. System shows:
   - Expected cash (calculated from transactions)
   - Difference (positive = over, negative = short)
6. Add notes explaining any discrepancy
7. Click **Close Shift**

#### Viewing Shift Reports (Z-Report)

1. In Shift History, find the shift you want
2. Click **View Report**
3. The Z-Report shows:
   - Shift duration (start to end time)
   - Cash summary (opening → closing)
   - Sales breakdown by payment method
   - Refunds processed
   - Total transactions and average sale value
4. Click **Print Report** if needed

### How to Use (Code)

```php
use App\Services\POSSessionService;

$sessionService = app(POSSessionService::class);

// Open a shift
$session = $sessionService->openSession(
    user: auth()->user(),
    openingCash: 50000,
    terminalId: 'POS-1'
);

// Check if user has an open session
if ($sessionService->hasOpenSession()) {
    $currentSession = $sessionService->getCurrentSession();
}

// Record cash movements
$sessionService->recordCashIn($session, 10000, 'Petty cash deposit');
$sessionService->recordCashOut($session, 5000, 'Change for customer');

// Get real-time summary
$summary = $sessionService->getSessionSummary($session);
// Returns: total_sales, total_refunds, cash_in, cash_out, expected_cash

// Close the shift
$sessionService->closeSession(
    session: $session,
    closingCash: 125000,
    closedBy: auth()->user(),
    notes: 'All accounted for'
);

// Get summary for a specific day
$dailySummary = $sessionService->getDailySummary(today());
```

### Best Practices

| Do | Don't |
|----|-------|
| Count cash carefully before opening | Guess at opening amounts |
| Document every cash in/out with notes | Move cash without recording |
| Close shift before leaving for the day | Leave shifts open overnight |
| Explain any variance in notes | Ignore discrepancies |
| Keep register area secure | Leave drawer unlocked |

---

## Feature 2: Split Payments

### What Is It?

Split payments allow customers to pay using multiple methods in a single transaction. For example, a customer can pay 50,000 IQD in cash and the remaining 30,000 IQD by card.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Customer Convenience** | Don't lose sales when customers lack full payment in one method |
| **Flexibility** | Accept any combination of payment types |
| **Accurate Tracking** | Each payment method tracked separately |
| **Change Calculation** | Automatic change calculation for cash portions |

### When to Use

| Scenario | Use Split Payment |
|----------|-------------------|
| Customer doesn't have enough cash | Add card payment for remainder |
| Customer wants to use gift card + cash | Split between both |
| Customer has store credit to use | Apply credit + collect balance |
| Group payment situations | Each person pays their share |

### Use Case: Mixed Payment at Restaurant

**Scenario:** Total bill is 80,000 IQD. Customer has 50,000 IQD cash and wants to pay the rest by card.

**Solution:**
1. Enable split payment
2. Add cash payment: 50,000 IQD (customer gives exactly)
3. Add card payment: 30,000 IQD
4. Complete sale
5. Receipt shows both payment methods

### How to Use (UI Steps)

1. In POS, add items to cart as normal
2. Click **Payment**
3. Toggle **Split Payment** option ON
4. For each payment:
   - Select payment method (Cash, Card, Bank Transfer, Mobile, Credit)
   - Enter amount for this payment
   - For cash: enter tendered amount to calculate change
   - Click **Add Payment**
5. Continue until total is covered (remaining shows 0)
6. Click **Complete Sale**

### Available Payment Methods

| Method | Code | Description | When to Use |
|--------|------|-------------|-------------|
| Cash | `cash` | Physical currency | In-person cash payments |
| Card | `card` | Credit/Debit card | Card swipe/tap payments |
| Bank Transfer | `bank_transfer` | Direct bank transfer | Pre-arranged transfers |
| Mobile Payment | `mobile_payment` | FIB, FastPay, etc. | Mobile wallet payments |
| Credit | `credit` | Store credit/account | Customer has credit balance |

### How to Use (Code)

```php
use App\Services\SplitPaymentService;

$paymentService = app(SplitPaymentService::class);

// Process multiple payments for a sale
$payments = $paymentService->processPayments($sale, [
    [
        'method' => 'cash',
        'amount' => 50000,
        'tendered' => 50000, // Exact amount
    ],
    [
        'method' => 'card',
        'amount' => 30000,
        'reference_number' => 'TXN123456',
    ],
], $currentSession);

// Validate payments before processing
$errors = $paymentService->validatePayments($saleTotal, $payments);
if (!empty($errors)) {
    // Handle validation errors
}

// Calculate change for cash payment
$change = $paymentService->calculateChange(
    tenderedAmount: 100000,
    dueAmount: 85000
); // Returns 15000

// Get payment breakdown for a sale
$breakdown = $paymentService->getPaymentBreakdown($sale);
// Returns: total_sale, total_paid, remaining, is_fully_paid, by_method

// Add additional payment to existing sale
$payment = $paymentService->addPayment($sale, [
    'method' => 'bank_transfer',
    'amount' => 20000,
    'reference_number' => 'TRANSFER-789',
]);

// Void a payment (with reason)
$paymentService->voidPayment($payment, $currentSession);
```

### Best Practices

| Do | Don't |
|----|-------|
| Verify total matches sale amount | Accept partial payment without arrangement |
| Record reference numbers for card/transfer | Skip reference numbers |
| Count cash received carefully | Rush through cash counting |
| Keep payment records for disputes | Delete payment records |

---

## Feature 3: Customer Loyalty Program

### What Is It?

The loyalty system rewards customers for purchases. Customers earn points when they buy, and redeem points for discounts on future purchases. The system supports tiers (Bronze, Silver, Gold, Platinum) for premium customers.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Customer Retention** | Points give customers a reason to return |
| **Increased Sales** | Customers spend more to earn/use points |
| **Customer Data** | Track purchase history and preferences |
| **Competitive Advantage** | Differentiate from competitors without loyalty programs |
| **Targeted Marketing** | Identify and reward your best customers |

### When to Use

| Scenario | Action |
|----------|--------|
| Customer makes a purchase | Points automatically awarded |
| Customer wants to redeem points | Apply points as discount |
| New customer wants to join | Enroll them in program |
| Running a promotion | Award bonus points |
| Customer returns item | Points reversed automatically |

### Use Case: Building Customer Loyalty

**Scenario:** Coffee shop wants to encourage repeat visits.

**Setup:**
- Program name: "Coffee Rewards"
- Earn: 1 point per 1,000 IQD spent
- Redeem: Each point worth 100 IQD
- Minimum redemption: 50 points (5,000 IQD value)

**Customer Journey:**
1. First visit: Spends 25,000 IQD → Earns 25 points
2. Second visit: Spends 30,000 IQD → Earns 30 points (total: 55)
3. Third visit: Has 55 points → Redeems 50 points for 5,000 IQD off

**Result:** Customer saved 5,000 IQD and will likely return again.

### How Points Work

#### Earning Points

Points are calculated automatically based on purchase amount:

```
Points Earned = Purchase Amount × Points Per Currency

Example:
- Points per currency: 0.01 (1 point per 100 IQD)
- Purchase: 50,000 IQD
- Points earned: 50,000 × 0.01 = 500 points
```

#### Redeeming Points

Points convert to discount value:

```
Discount Value = Points × Currency Per Point

Example:
- Currency per point: 10 (each point = 10 IQD)
- Points to redeem: 500
- Discount: 500 × 10 = 5,000 IQD off
```

#### Tier System

Customers progress through tiers based on lifetime points:

| Tier | Lifetime Points | Benefits |
|------|-----------------|----------|
| Bronze | 0+ | Base earning rate |
| Silver | 1,000+ | Priority service |
| Gold | 5,000+ | Bonus points on birthdays |
| Platinum | 10,000+ | Exclusive offers |

### How to Use (UI Steps)

#### Setting Up the Program

1. Create a loyalty program (usually done once by admin)
2. Configure earning rate (points per currency)
3. Set redemption value (currency per point)
4. Define minimum points for redemption
5. Set up tiers (optional)

#### At Point of Sale

**For earning points:**
1. Select or create customer on the sale
2. Process sale normally
3. Points are automatically calculated and awarded
4. Receipt shows points earned

**For redeeming points:**
1. Select customer (must have enough points)
2. Click **Redeem Points** in payment
3. Enter points to redeem or select amount
4. Discount is applied to sale
5. Complete payment

#### Viewing Customer Loyalty Status

1. Find customer in customer list
2. View their loyalty dashboard:
   - Current points balance
   - Lifetime points earned
   - Current tier
   - Points value (in IQD)
   - Transaction history

### How to Use (Code)

```php
use App\Services\LoyaltyService;

$loyaltyService = app(LoyaltyService::class);

// Enroll customer in loyalty program
$loyalty = $loyaltyService->enrollCustomer($customer);

// Award points for a sale (usually automatic)
$transaction = $loyaltyService->awardPointsForSale($sale);

// Check customer loyalty status
$summary = $loyaltyService->getCustomerSummary($customer);
// Returns: enrolled, points_balance, lifetime_points, tier, points_value, can_redeem

// Check if customer can redeem
if ($loyaltyService->canRedeem($customer, 500)) {
    $value = $loyaltyService->calculateRedemptionValue($customer, 500);
    $transaction = $loyaltyService->redeemPoints($customer, 500, $sale);
}

// Add bonus points (promotions, birthdays)
$loyaltyService->addBonusPoints($customer, 100, 'Birthday bonus');

// Adjust points (corrections)
$loyaltyService->adjustPoints($customer, -50, 'Correction for returned item');

// Get transaction history
$history = $loyaltyService->getTransactionHistory($customer, limit: 20);

// Get tier progress
$progress = $loyaltyService->getTierProgress($customer);
// Returns: current_tier, next_tier, points_to_next_tier, progress_percentage

// Reverse points when sale is refunded
$loyaltyService->reverseSalePoints($sale);

// Expire old points (run daily via scheduler)
$expiredCount = $loyaltyService->expirePoints(daysOld: 365);
```

### Displaying Loyalty in POS

```blade
@if($customer && $loyalty = $customer->loyalty)
    <div class="loyalty-info">
        <span class="tier">{{ $loyalty->tier ?? 'Member' }}</span>
        <span class="points">{{ $loyalty->points_balance }} points</span>
        <span class="value">({{ number_format($loyalty->pointsValue, 0) }} IQD)</span>
    </div>
@endif
```

### Best Practices

| Do | Don't |
|----|-------|
| Promote the program to all customers | Keep the program a secret |
| Display points balance on receipts | Make redemption complicated |
| Train staff on how points work | Let points expire silently |
| Award bonus points for promotions | Make earning points too hard |
| Track program effectiveness | Ignore participation rates |

---

## Feature 4: Hold/Recall Sales

### What Is It?

Hold/Recall allows cashiers to pause a sale in progress and resume it later. The cart items and customer info are saved, and can be loaded back into the POS when ready.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Don't Lose Sales** | Customer can continue shopping without losing cart |
| **Serve Other Customers** | Free up register while waiting |
| **Reduce Errors** | Items stay in system, no re-scanning needed |
| **Customer Service** | Accommodates customers who need to step away |

### When to Use

| Scenario | Action |
|----------|--------|
| Customer forgot wallet, going to car | Hold sale, serve next customer |
| Customer wants to grab more items | Hold sale, they continue shopping |
| Customer needs to check with someone | Hold sale, they make a call |
| Long checkout needs manager override | Hold sale, get manager |
| End of day with unfinished sale | Sales auto-expire after set time |

### Use Case: Customer Forgets Wallet

**Scenario:** Customer has 50,000 IQD of items scanned but forgot wallet in car.

**Steps:**
1. Cashier clicks **Hold Sale**
2. Adds note: "Customer getting wallet from car"
3. Serves next customer in line
4. Original customer returns 5 minutes later
5. Cashier clicks **Held Sales** → **Resume**
6. All items restored, complete the sale

**Benefit:** No items lost, no re-scanning, line kept moving.

### How to Use (UI Steps)

#### Holding a Sale

1. While in POS with items in cart
2. Click **Hold Sale** button
3. Optionally add a reference note (e.g., "Customer name", "Reason")
4. Cart is saved and cleared
5. You can now start a new sale

#### Recalling a Held Sale

1. Click **Held Sales** button
2. View list showing:
   - Customer name (if assigned)
   - Number of items
   - Total amount
   - Time held
   - Notes
3. Click **Resume** on the sale you want
4. Cart is restored with all items
5. Complete the sale normally

#### Managing Held Sales

- Held sales expire after 24 hours (configurable)
- Delete held sales manually if no longer needed
- View who held the sale and when

### How to Use (Code)

```php
use App\Models\HeldSale;

// Hold a sale
$heldSale = HeldSale::create([
    'user_id' => auth()->id(),
    'customer_id' => $customerId,
    'cart_data' => $cartItems, // JSON array of cart items
    'form_data' => $formData,  // JSON of form state
    'total_amount' => $total,
    'notes' => 'Customer getting more items',
    'expires_at' => now()->addHours(24),
]);

// List held sales for current user
$heldSales = HeldSale::active()
    ->forUser(auth()->user())
    ->latest()
    ->get();

// Resume a held sale
$heldSale = HeldSale::find($id);
$cartData = $heldSale->cart_data;
$formData = $heldSale->form_data;

// Delete after successfully resuming
$heldSale->delete();

// Clean up expired sales (run via scheduler)
HeldSale::where('expires_at', '<', now())->delete();
```

### Best Practices

| Do | Don't |
|----|-------|
| Add descriptive notes | Leave notes blank |
| Check held sales before shift end | Let held sales expire unnecessarily |
| Use for short delays only | Use as long-term order storage |
| Clean up abandoned holds | Let holds pile up |

---

## Feature 5: Receipt Printing

### What Is It?

The receipt system generates professional receipts in multiple formats to work with any printer type - from browser printing to thermal receipt printers.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Universal Compatibility** | Works with any printer type |
| **Professional Appearance** | Clean, branded receipts |
| **Multiple Formats** | HTML for browser, ESC/POS for thermal printers |
| **Customizable** | Configure header, footer, what to show |
| **Loyalty Integration** | Show points earned on receipt |

### When to Use

| Scenario | Format |
|----------|--------|
| Customer wants paper receipt | Thermal (ESC/POS) or Browser (HTML) |
| Email receipt to customer | HTML |
| Record keeping | Any format |
| Gift receipts (no prices) | HTML with customization |

### Output Formats

| Format | Best For | Description |
|--------|----------|-------------|
| **HTML** | Browser printing, email | Standard web format |
| **Raw Text** | Text-only printers | Plain text, fixed width |
| **ESC/POS** | Thermal printers | Industry-standard commands |

### Use Case: Thermal Receipt Printing

**Scenario:** Customer completes purchase, needs printed receipt.

**Process:**
1. Sale is completed
2. System generates receipt data
3. Converts to ESC/POS commands
4. Sends to thermal printer
5. Receipt prints with:
   - Business name and address
   - Date, time, receipt number
   - Items with quantities and prices
   - Tax breakdown
   - Total and payment method
   - Loyalty points earned
   - Thank you message
   - Barcode for returns

### How to Use (Code)

```php
use App\Services\ReceiptPrinterService;

$receiptService = app(ReceiptPrinterService::class);

// Configure receipt (optional - uses defaults if not set)
$receiptService->setConfig([
    'business_name' => 'My Store',
    'paper_width' => 48, // characters for thermal
    'footer_message' => 'Thank you for shopping!',
    'show_tax_breakdown' => true,
    'show_loyalty_points' => true,
]);

// Generate complete receipt data
$receipt = $receiptService->generateReceipt($sale);

// Access specific formats
$html = $receipt['html'];
$text = $receipt['raw_text'];
$escpos = $receipt['escpos']['commands'];
$escposBase64 = $receipt['escpos']['base64'];

// Or generate individual formats
$html = $receiptService->generateHtml($sale);
$text = $receiptService->generateRawText($sale);
$escpos = $receiptService->generateEscPos($sale);
```

### Receipt Structure

```php
$receipt = [
    'header' => [
        'business_name' => 'Store Name',
        'receipt_number' => 'INV-0001',
        'date' => '2026-01-11',
        'time' => '14:30:00',
        'cashier' => 'Ahmad',
        'terminal_id' => 'POS-1',
        'customer_name' => 'John Doe',
    ],
    'items' => [
        [
            'name' => 'Product A',
            'quantity' => 2,
            'unit_price' => 5000,
            'discount' => 0,
            'tax_amount' => 750,
            'line_total' => 10750,
        ],
    ],
    'totals' => [
        'sub_total' => 10000,
        'discount' => 0,
        'tax_amount' => 750,
        'total' => 10750,
    ],
    'payments' => [
        [
            'method' => 'Cash',
            'amount' => 11000,
            'tendered' => 11000,
            'change' => 250,
        ],
    ],
    'footer' => [
        'message' => 'Thank you for your business!',
        'barcode' => 'INV-0001',
        'loyalty' => [
            'points_earned' => 10,
            'message' => 'You earned 10 points!',
        ],
    ],
];
```

### Browser Printing (HTML)

```javascript
function printReceipt(html) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Receipt</title>
                <style>
                    body {
                        font-family: monospace;
                        width: 80mm;
                        margin: 0 auto;
                    }
                    @media print {
                        body { margin: 0; }
                    }
                </style>
            </head>
            <body>${html}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
```

### Thermal Printer Integration

For direct thermal printing, send ESC/POS commands via:

**1. WebUSB API (Chrome)**

```javascript
async function printToThermal(base64Commands) {
    const device = await navigator.usb.requestDevice({
        filters: [{ vendorId: 0x0483 }] // Your printer vendor
    });

    await device.open();
    await device.selectConfiguration(1);
    await device.claimInterface(0);

    const data = Uint8Array.from(atob(base64Commands), c => c.charCodeAt(0));
    await device.transferOut(1, data);

    await device.close();
}
```

**2. Network Printer**

```php
// Send directly to network printer
$socket = fsockopen($printerIp, 9100);
fwrite($socket, $escposCommands);
fclose($socket);
```

---

## Technical Reference

### Database Schema

#### pos_sessions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Cashier user ID |
| terminal_id | string | Terminal identifier |
| opening_time | timestamp | Shift start time |
| closing_time | timestamp | Shift end time |
| opening_cash | decimal | Starting cash amount |
| closing_cash | decimal | Counted closing amount |
| expected_cash | decimal | Calculated expected cash |
| cash_difference | decimal | Over/short amount |
| status | enum | open, closed, suspended |
| closed_by | bigint | User who closed shift |
| notes | text | Closing notes |

#### pos_session_transactions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| pos_session_id | bigint | Parent session |
| type | enum | sale, refund, cash_in, cash_out |
| reference_type | string | Related model class |
| reference_id | bigint | Related model ID |
| amount | decimal | Transaction amount |
| payment_method | string | Payment method used |
| currency_id | bigint | Currency used |
| exchange_rate | decimal | Rate at transaction time |
| notes | text | Transaction notes |

#### sale_payments

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| sale_id | bigint | Parent sale |
| pos_session_id | bigint | Associated session |
| payment_method | string | cash, card, etc. |
| amount | decimal | Payment amount |
| currency_id | bigint | Currency used |
| exchange_rate | decimal | Rate used |
| amount_in_base_currency | decimal | Converted amount |
| reference_number | string | Card/transfer reference |
| tendered_amount | decimal | Cash given |
| change_amount | decimal | Change returned |
| notes | text | Payment notes |

#### held_sales

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Cashier who held |
| customer_id | bigint | Customer (optional) |
| cart_data | json | Cart items |
| form_data | json | Form state |
| total_amount | decimal | Cart total |
| notes | string | Reference notes |
| expires_at | timestamp | Auto-delete time |

#### loyalty_programs

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Program name |
| name_ar | string | Arabic name |
| name_ckb | string | Kurdish name |
| type | enum | points, cashback, tiered |
| points_per_currency | decimal | Earning rate |
| currency_per_point | decimal | Redemption value |
| min_redemption_points | int | Minimum to redeem |
| is_active | boolean | Program active |
| settings | json | Tier config, etc. |

#### customer_loyalty

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| customer_id | bigint | Customer |
| loyalty_program_id | bigint | Program |
| points_balance | int | Current points |
| lifetime_points | int | Total earned |
| tier | string | Current tier |
| tier_updated_at | timestamp | Last tier change |

#### loyalty_transactions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| customer_loyalty_id | bigint | Parent loyalty record |
| type | enum | earn, redeem, expire, adjust, bonus |
| points | int | Points (+ or -) |
| sale_id | bigint | Related sale |
| description | string | Transaction reason |
| created_at | timestamp | Transaction time |

---

## Troubleshooting

### Shift won't open

**Problem:** Cannot open a new shift.

**Solutions:**
1. Check if you have an existing open shift (close it first)
2. Verify you have `pos.access` permission
3. Clear cache: `php artisan optimize:clear`

---

### Split payment total doesn't match

**Problem:** After adding all payments, remaining amount isn't zero.

**Solutions:**
1. Verify each payment amount entered correctly
2. Check currency conversion rates if using multiple currencies
3. Use the validate function before processing

---

### Loyalty points not awarding

**Problem:** Customer made purchase but didn't earn points.

**Solutions:**
1. Verify customer was linked to the sale
2. Check loyalty program is active
3. Ensure `points_per_currency` > 0 in program settings
4. Verify customer is enrolled in the program

---

### Receipt not printing

**Problem:** Receipt won't print to thermal printer.

**Solutions:**
1. Check printer is connected and powered on
2. Verify ESC/POS commands match your printer model
3. Test with raw text format first
4. Check printer's IP/USB connection settings

---

### Held sale disappeared

**Problem:** A held sale is no longer in the list.

**Solutions:**
1. Check if it expired (default: 24 hours)
2. Another user may have resumed it
3. Someone may have deleted it
4. Check the held_sales table directly

---

*Last updated: January 2026*
