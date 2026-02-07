# Inventory Management Module

## Overview

The Inventory module provides enterprise-level stock control across multiple warehouses. Track products by batch/lot numbers, manage serial numbers with warranties, transfer stock between locations, and conduct physical inventory counts - all with complete audit trails.

---

## Why Use These Features?

### Key Benefits

| Benefit | How It Helps Your Business |
|---------|---------------------------|
| **Multi-Location Control** | Track stock across all your warehouses |
| **Product Traceability** | Know exactly where every item came from (batch/serial) |
| **Expiry Management** | Never sell expired products |
| **Cost Accuracy** | Multiple costing methods for financial accuracy |
| **Loss Prevention** | Regular counts catch discrepancies early |
| **Automation** | Reorder rules prevent stockouts |

### Problems It Solves

- **"I don't know which warehouse has stock"** - Real-time stock by location
- **"Customer returned expired product"** - Batch tracking with expiry dates
- **"Warranty claims are a nightmare"** - Serial tracking with warranty dates
- **"Stock counts never match"** - Variance analysis and adjustments
- **"We keep running out of popular items"** - Automated reorder alerts
- **"Moving stock between stores is chaos"** - Formal transfer process with approval

---

## Who Should Read This?

| Role | Relevant Sections |
|------|-------------------|
| **Warehouse Staff** | All sections |
| **Inventory Managers** | All sections + Costing Methods |
| **Store Managers** | Multi-Warehouse, Stock Transfers |
| **Finance Team** | Costing Methods, Stock Valuation |
| **Developers** | Technical Reference sections |

---

## Module Components

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       Inventory Management                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌────────────────────┐   Track stock across multiple locations         │
│  │  Multi-Warehouse   │   USE WHEN: You have more than one store        │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Group products by batch with expiry dates     │
│  │  Batch/Lot Track   │   USE WHEN: Products expire (food, medicine)    │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Track individual units by serial number       │
│  │  Serial Tracking   │   USE WHEN: High-value items or warranties      │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Move inventory between warehouses             │
│  │  Stock Transfers   │   USE WHEN: Rebalancing stock or fulfilling     │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Physical counts with variance adjustment      │
│  │  Stock Counts      │   USE WHEN: Monthly/annual inventory audits     │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   FIFO, LIFO, AVCO, Specific                    │
│  │  Costing Methods   │   USE WHEN: Financial reporting requirements    │
│  └────────────────────┘                                                 │
│                                                                         │
│  ┌────────────────────┐   Automatic low-stock alerts                    │
│  │  Reorder Rules     │   USE WHEN: Preventing stockouts               │
│  └────────────────────┘                                                 │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Feature 1: Multi-Warehouse Management

### What Is It?

Multi-warehouse management lets you track inventory separately at each physical location (warehouses, stores, distribution centers). Each location has its own stock levels, and you can see total inventory across all locations.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Visibility** | Know stock levels at each location instantly |
| **Allocation** | Sell from the right location |
| **Planning** | Identify which locations need replenishment |
| **Organization** | Bin locations within warehouses for easy picking |
| **Flexibility** | Each warehouse can have different rules |

### When to Use

| Scenario | Action |
|----------|--------|
| You have multiple stores | Create a warehouse for each store |
| You have a central warehouse + retail | Separate warehouses for each |
| You want to track by room/area | Use warehouse locations (bins) |
| Stock is at different addresses | Separate warehouses |
| You need to prevent overselling | Disable negative stock per warehouse |

### Use Case: Retail Chain with Central Warehouse

**Scenario:** 3 retail stores + 1 central warehouse

**Setup:**
1. Create "Main Warehouse" (default, where purchases arrive)
2. Create "Store A", "Store B", "Store C" warehouses
3. Purchases received at Main Warehouse
4. Stock transferred to stores as needed
5. Each store sells from its own inventory

**Benefits:**
- Know exactly what's at each location
- Transfer stock when one store runs low
- Main warehouse handles bulk receiving

### How to Use (UI Steps)

#### Creating a Warehouse

1. Navigate to **Inventory > Warehouses**
2. Click **Create Warehouse**
3. Fill in details:
   - **Name** (in all languages)
   - **Code** (unique identifier like "WH-001")
   - **Address** and contact info
   - **Manager** (optional - assign responsible person)
   - **Is Default** (where new stock goes by default)
   - **Allow Negative Stock** (disable to prevent overselling)
4. Click **Create**

#### Setting Up Locations (Bins)

For organizing within a warehouse:

1. Edit the warehouse
2. Go to **Locations** tab
3. Add locations with:
   - **Name** (e.g., "A-01-001")
   - **Aisle/Shelf/Bin** (e.g., Aisle A, Shelf 01, Bin 001)
4. Use these when receiving or counting stock

#### Viewing Stock by Warehouse

1. Go to any product
2. View the **Stock** tab
3. See quantity at each warehouse
4. View reserved vs. available

### How to Use (Code)

```php
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\ProductWarehouse;

// Create a warehouse
$warehouse = Warehouse::create([
    'name' => 'Main Warehouse',
    'name_ar' => 'المستودع الرئيسي',
    'name_ckb' => 'کۆگای سەرەکی',
    'code' => 'WH-001',
    'address' => '123 Industrial Zone',
    'phone' => '+964 750 123 4567',
    'manager_id' => $userId,
    'is_default' => true,
    'is_active' => true,
    'allow_negative_stock' => false,
]);

// Get default warehouse
$defaultWarehouse = Warehouse::getDefault();

// Get all active warehouses
$warehouses = Warehouse::active()->get();

// Create a location within warehouse
$location = WarehouseLocation::create([
    'warehouse_id' => $warehouse->id,
    'name' => 'A-01-001',
    'aisle' => 'A',
    'shelf' => '01',
    'bin' => '001',
    'is_active' => true,
]);

// Get full path
echo $location->full_path; // "Main Warehouse > A-01-001"

// Get product stock in a warehouse
$stock = ProductWarehouse::where('product_id', $product->id)
    ->where('warehouse_id', $warehouse->id)
    ->first();

echo $stock->quantity;          // Total quantity
echo $stock->reserved_quantity; // Reserved for orders
echo $stock->available;         // Available for sale

// Check if product needs reorder in this warehouse
if ($stock->needsReorder()) {
    // Send alert or create purchase order
}
```

### Best Practices

| Do | Don't |
|----|-------|
| Set one warehouse as default | Leave all warehouses without a default |
| Use meaningful location codes | Use random location names |
| Assign managers to warehouses | Leave warehouses unmanaged |
| Disable negative stock for retail | Allow negative stock without reason |
| Regular audits per location | Ignore discrepancies |

---

## Feature 2: Batch/Lot Tracking

### What Is It?

Batch tracking groups products by their production batch or lot number. Each batch has its own manufacture date, expiry date, and cost. Essential for food, pharmaceuticals, and any products with expiration dates.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Traceability** | Know which batch was sold to which customer |
| **Expiry Control** | Sell oldest batches first (FIFO) |
| **Recalls** | Quickly identify affected sales if batch has issues |
| **Cost Tracking** | Each batch can have different costs |
| **Compliance** | Required for food/pharma regulations |

### When to Use

| Product Type | Use Batch Tracking? |
|--------------|---------------------|
| Food items | Yes - expiry dates |
| Medicines/pharmaceuticals | Yes - expiry + recalls |
| Perishable goods | Yes - expiry dates |
| Products with lot numbers | Yes - traceability |
| Generic items (pens, screws) | Usually not needed |
| Unique items | Use serial tracking instead |

### Use Case: Pharmacy Inventory

**Scenario:** Pharmacy needs to track medicine batches for expiry and recalls

**Setup:**
1. Enable batch tracking on all medicine products
2. Set expiry warning to 90 days
3. When receiving from supplier, record batch number and expiry

**Daily Operations:**
1. System alerts when batches are expiring soon
2. Pharmacist moves expiring batches to front
3. If recall issued, find which customers bought that batch

**Benefits:**
- Never sell expired medicine
- Quick recall response
- Regulatory compliance

### How to Use (UI Steps)

#### Enabling Batch Tracking on a Product

1. Edit the product
2. Enable **Track Batches**
3. If product expires:
   - Enable **Has Expiry**
   - Set **Expiry Warning Days** (e.g., 30 days)
4. Save

#### Recording Batches When Receiving

1. Create a purchase order
2. When receiving, for each batch-tracked item:
   - Enter **Batch Number**
   - Enter **Manufacture Date** (optional)
   - Enter **Expiry Date** (if applicable)
   - System assigns the purchase cost to the batch
3. Complete receiving

#### Viewing Expiring Batches

1. Go to **Inventory > Reports > Expiring Batches**
2. Filter by date range (e.g., next 30 days)
3. See list of batches expiring soon
4. Take action (discount sale, transfer, disposal)

### How to Use (Code)

```php
use App\Models\ProductBatch;
use App\Services\InventoryService;

// Enable batch tracking on product
$product->update([
    'track_batches' => true,
    'has_expiry' => true,
    'expiry_warning_days' => 30,
]);

// Create a batch
$batch = ProductBatch::create([
    'product_id' => $product->id,
    'warehouse_id' => $warehouse->id,
    'batch_number' => 'BATCH-2026-001',
    'manufacture_date' => '2026-01-01',
    'expiry_date' => '2027-01-01',
    'quantity' => 100,
    'unit_cost' => 25.50,
    'purchase_id' => $purchase->id,
]);

// Check expiry status
if ($batch->isExpired()) {
    // Handle expired batch - don't sell!
}

if ($batch->isExpiringSoon()) {
    // Send warning notification
}

echo $batch->days_until_expiry; // Days remaining

// Get expiring batches (within 30 days)
$expiringBatches = ProductBatch::active()
    ->withStock()
    ->expiringSoon(30)
    ->get();

// Get expired batches (should not be sold)
$expiredBatches = ProductBatch::active()
    ->withStock()
    ->expired()
    ->get();

// FIFO ordering (oldest expiry first)
$fifoBatches = ProductBatch::fifo()->get();

// LIFO ordering (newest first)
$lifoBatches = ProductBatch::lifo()->get();

// Using InventoryService
$inventoryService = app(InventoryService::class);

// Create batch with service
$batch = $inventoryService->createBatch(
    product: $product,
    batchNumber: 'BATCH-001',
    quantity: 100,
    unitCost: 25.00,
    warehouse: $warehouse,
    manufactureDate: now(),
    expiryDate: now()->addYear(),
    purchase: $purchase
);

// Get expiring batches via service
$expiring = $inventoryService->getExpiringBatches(30, $warehouse);
```

### Best Practices

| Do | Don't |
|----|-------|
| Set realistic warning days | Wait until last day |
| Review expiring batches weekly | Ignore expiry reports |
| Sell FIFO (oldest first) | Sell randomly |
| Document disposal of expired items | Dispose without records |
| Train staff on batch importance | Let staff ignore batch selection |

---

## Feature 3: Serial Number Tracking

### What Is It?

Serial tracking assigns a unique identifier to each individual unit of a product. Unlike batches (groups of items), each serial number represents exactly one physical item with its own warranty, status, and history.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Individual Tracking** | Know the history of each unit |
| **Warranty Management** | Track warranty start/end per unit |
| **Return Handling** | Verify customer returns by serial |
| **Theft Prevention** | Can't swap items without detection |
| **Service History** | Track repairs and returns |

### When to Use

| Product Type | Use Serial Tracking? |
|--------------|----------------------|
| Electronics (phones, laptops) | Yes - warranty |
| Appliances | Yes - warranty |
| High-value items | Yes - theft prevention |
| Vehicles/parts | Yes - registration |
| Bulk commodities | No - use batch instead |
| Low-value items | No - overhead not worth it |

### Use Case: Electronics Retailer

**Scenario:** Sell phones with 1-year warranty

**At Purchase:**
1. Receive 50 phones from supplier
2. Scan each serial number into system
3. Each phone linked to purchase order and cost

**At Sale:**
1. Customer buys a phone
2. Scan serial number
3. Serial linked to customer and sale
4. Warranty starts from sale date

**Warranty Claim:**
1. Customer brings phone with issue
2. Scan serial number
3. System shows: purchase date, sale date, warranty end
4. If under warranty, process claim

### How to Use (UI Steps)

#### Enabling Serial Tracking on a Product

1. Edit the product
2. Enable **Track Serials**
3. Set **Warranty Months** (e.g., 12)
4. Save

#### Recording Serials When Receiving

1. Create a purchase and receive items
2. For serial-tracked products:
   - Scan or enter each serial number
   - System creates a serial record per unit
   - All serials linked to this purchase
3. Complete receiving

#### Selling Serial-Tracked Items

1. Add product to cart
2. System prompts for serial number
3. Scan or select the serial
4. Serial is now linked to this sale and customer
5. Warranty countdown begins

#### Checking Warranty Status

1. Go to **Inventory > Serial Numbers**
2. Search by serial number
3. View:
   - Current status (available, sold, returned)
   - Purchase information
   - Sale information (if sold)
   - Warranty start and end dates
   - Is under warranty?

### How to Use (Code)

```php
use App\Models\ProductSerial;
use App\Services\InventoryService;

// Enable serial tracking on product
$product->update([
    'track_serials' => true,
    'warranty_months' => 12,
]);

// Create a serial number
$serial = ProductSerial::create([
    'product_id' => $product->id,
    'warehouse_id' => $warehouse->id,
    'batch_id' => $batch->id, // Optional - link to batch
    'serial_number' => 'SN-2026-00001',
    'status' => ProductSerial::STATUS_AVAILABLE,
    'cost' => 150.00,
    'purchase_id' => $purchase->id,
    'warranty_start' => now(),
    'warranty_end' => now()->addMonths(12),
]);

// Serial statuses
ProductSerial::STATUS_AVAILABLE; // In stock, ready to sell
ProductSerial::STATUS_SOLD;      // Sold to customer
ProductSerial::STATUS_RESERVED;  // Reserved for order
ProductSerial::STATUS_DAMAGED;   // Damaged/defective
ProductSerial::STATUS_RETURNED;  // Returned by customer

// Check warranty status
if ($serial->isUnderWarranty()) {
    echo "Warranty valid until: " . $serial->warranty_end;
}

// Query serials by status
$availableSerials = ProductSerial::available()->get();
$soldSerials = ProductSerial::sold()->get();

// Find by serial number
$serial = ProductSerial::findBySerial('SN-2026-00001');

// Create multiple serials via service
$inventoryService = app(InventoryService::class);
$serials = $inventoryService->createSerials(
    product: $product,
    serialNumbers: ['SN-001', 'SN-002', 'SN-003'],
    cost: 150.00,
    warehouse: $warehouse,
    batch: $batch,
    purchase: $purchase
);
```

### Best Practices

| Do | Don't |
|----|-------|
| Scan serials at receiving | Manually type serials |
| Verify serial matches product | Assume serial is correct |
| Train staff on serial importance | Skip serial for speed |
| Check serial before accepting returns | Accept returns without serial |
| Use barcode scanners | Rely on manual entry |

---

## Feature 4: Stock Transfers

### What Is It?

Stock transfers move inventory from one warehouse to another with a formal approval process. The system tracks what was requested, sent, and received - with variances documented.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Formal Process** | Clear accountability for movements |
| **Approval Workflow** | Managers approve before transfer |
| **Variance Tracking** | Know if quantities don't match |
| **Audit Trail** | Who transferred what, when |
| **Planning** | Expected arrival dates |

### When to Use

| Scenario | Use Stock Transfer |
|----------|-------------------|
| Move stock between warehouses | Yes |
| Replenish retail store from warehouse | Yes |
| Return damaged goods to warehouse | Yes |
| Rebalance stock across locations | Yes |
| Internal adjustment (same warehouse) | No - use stock count |
| Customer return | No - use returns process |

### Use Case: Replenishing Retail Store

**Scenario:** Store A is running low on popular product

**Process:**
1. Store A manager requests transfer from Main Warehouse
2. Warehouse manager reviews and approves
3. Warehouse picks and ships items
4. Store A receives and verifies
5. Stock levels update automatically

### Transfer Workflow

```
┌─────────┐    ┌─────────┐    ┌───────────┐    ┌──────────┐    ┌───────────┐
│  Draft  │ →  │ Pending │ →  │ In Transit│ →  │ Partial  │ →  │ Completed │
└─────────┘    └─────────┘    └───────────┘    └──────────┘    └───────────┘
   Create       Awaiting        Items           Some items       All items
  transfer      approval        shipped         received         received

              ↓ Can be cancelled at any stage before completion ↓
                                    ┌───────────┐
                                    │ Cancelled │
                                    └───────────┘
```

### How to Use (UI Steps)

#### Creating a Transfer Request

1. Navigate to **Inventory > Stock Transfers**
2. Click **Create Transfer**
3. Select:
   - **From Warehouse** (source)
   - **To Warehouse** (destination)
   - **Expected Date** (when it should arrive)
4. Add items:
   - Select product
   - Select batch (if batch-tracked)
   - Enter quantity
5. Add notes if needed
6. Save as **Draft** or submit for approval

#### Approving a Transfer

1. View pending transfers
2. Review items and quantities
3. Click **Approve** or **Reject**
4. If approved, status changes to **Pending** → **In Transit** when shipped

#### Shipping (Sending)

1. Open the approved transfer
2. For each item, enter **Quantity Sent**
3. For serial-tracked items, select which serials
4. Click **Mark as Shipped**
5. Status changes to **In Transit**

#### Receiving

1. When items arrive at destination
2. Open the transfer
3. For each item, enter **Quantity Received**
4. Note any variances
5. Click **Complete**
6. Stock moves to destination warehouse

### How to Use (Code)

```php
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\InventoryService;

// Create transfer
$transfer = StockTransfer::create([
    'from_warehouse_id' => $sourceWarehouse->id,
    'to_warehouse_id' => $destWarehouse->id,
    'transfer_date' => now(),
    'expected_date' => now()->addDays(2),
    'notes' => 'Monthly stock rebalancing',
]);

// Add items
StockTransferItem::create([
    'stock_transfer_id' => $transfer->id,
    'product_id' => $product->id,
    'batch_id' => $batch->id, // Optional
    'quantity_requested' => 50,
    'unit_cost' => $product->average_cost,
]);

// For serial-tracked items
StockTransferItem::create([
    'stock_transfer_id' => $transfer->id,
    'product_id' => $product->id,
    'quantity_requested' => 3,
    'serial_ids' => [101, 102, 103], // Specific serial IDs
    'unit_cost' => $product->average_cost,
]);

// Approve transfer
$transfer->update([
    'status' => StockTransfer::STATUS_PENDING,
    'approved_by' => auth()->id(),
]);

// Mark in transit (shipping)
$transfer->update(['status' => StockTransfer::STATUS_IN_TRANSIT]);
foreach ($transfer->items as $item) {
    $item->update(['quantity_sent' => $item->quantity_requested]);
}

// Complete transfer (using InventoryService)
$inventoryService = app(InventoryService::class);
$inventoryService->transferStock($transfer);
```

### Best Practices

| Do | Don't |
|----|-------|
| Verify quantities at receipt | Trust sent quantities blindly |
| Document variances with notes | Ignore missing items |
| Use expected dates for planning | Leave dates blank |
| Approve transfers before shipping | Ship without approval |
| Complete transfers promptly | Leave transfers in limbo |

---

## Feature 5: Stock Counts

### What Is It?

Stock counts (physical inventory) compare system quantities with actual quantities on hand. The system calculates variances and allows posting adjustments to correct the inventory.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Accuracy** | Find and fix discrepancies |
| **Loss Detection** | Identify theft or shrinkage |
| **Compliance** | Required for audits |
| **Financial Accuracy** | Inventory value matches reality |
| **Operational Insight** | Understand where issues occur |

### When to Use

| Count Type | When | Description |
|------------|------|-------------|
| Full Count | Annually | Count everything in warehouse |
| Partial Count | Quarterly | Count specific categories |
| Cycle Count | Weekly/Monthly | Rotating selection of items |
| Spot Check | As needed | Random items for verification |

### Use Case: Annual Inventory Count

**Scenario:** Year-end inventory audit required

**Process:**
1. Create full stock count for main warehouse
2. System populates all products with system quantities
3. Teams count each item
4. Enter counted quantities
5. Review variances
6. Investigate significant differences
7. Post adjustments
8. Generate report for auditors

### How to Use (UI Steps)

#### Creating a Stock Count

1. Navigate to **Inventory > Stock Counts**
2. Click **Create Stock Count**
3. Select:
   - **Warehouse** to count
   - **Count Type** (Full, Partial, Cycle)
   - **Count Date**
4. Click **Initialize**
5. System creates count items with current system quantities

For Partial Count:
- Select specific products or categories to count

#### Performing the Count

1. Open the stock count
2. Click **Start Count**
3. For each item:
   - Go to location
   - Count physical quantity
   - Enter in **Counted Quantity** field
   - Add notes for any issues
4. System calculates variance automatically

#### Reviewing Variances

After counting:
1. View the **Variance Report**
2. Items with variance highlighted:
   - Positive = found more than expected
   - Negative = found less than expected
3. Investigate significant variances
4. Add explanation notes

#### Posting Adjustments

1. When satisfied with count accuracy
2. Click **Post Adjustments**
3. System creates inventory movements for variances
4. Stock levels updated to match counted quantities

### How to Use (Code)

```php
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Services\InventoryService;

$inventoryService = app(InventoryService::class);

// Create stock count
$stockCount = StockCount::create([
    'warehouse_id' => $warehouse->id,
    'count_type' => 'full', // full, partial, cycle
    'count_date' => now(),
    'status' => StockCount::STATUS_DRAFT,
]);

// Initialize count items from current stock
$inventoryService->initializeStockCount($stockCount);

// For partial count - specific products only
$inventoryService->initializeStockCount($stockCount, [1, 2, 3]);

// Start counting
$stockCount->update([
    'status' => StockCount::STATUS_IN_PROGRESS,
    'started_at' => now(),
]);

// Record counted quantities
foreach ($stockCount->items as $item) {
    $item->update([
        'counted_quantity' => $actualCount,
        'status' => StockCountItem::STATUS_COUNTED,
        'notes' => 'Found in wrong location',
    ]);
}

// Complete count
$stockCount->update([
    'status' => StockCount::STATUS_COMPLETED,
    'completed_at' => now(),
    'completed_by' => auth()->id(),
]);

// Check variances
foreach ($stockCount->items as $item) {
    echo "Product: " . $item->product->name;
    echo "System Qty: " . $item->system_quantity;
    echo "Counted Qty: " . $item->counted_quantity;
    echo "Variance: " . $item->variance;
    echo "Variance Value: " . $item->variance_value;
}

// Post adjustments to inventory
$inventoryService->postStockCountAdjustments($stockCount);
```

### Best Practices

| Do | Don't |
|----|-------|
| Count at quiet times | Count during peak hours |
| Freeze receiving during count | Accept shipments while counting |
| Double-count high-value items | Trust single count |
| Investigate all variances | Ignore small variances |
| Document reasons for variance | Post adjustments without explanation |

---

## Feature 6: Costing Methods

### What Is It?

Costing methods determine how inventory costs are calculated when items are sold or adjusted. Different methods produce different cost of goods sold (COGS) and inventory values.

### Available Methods

| Method | Full Name | Best For |
|--------|-----------|----------|
| **FIFO** | First In, First Out | Perishables, most businesses |
| **LIFO** | Last In, First Out | Tax optimization (where allowed) |
| **AVCO** | Weighted Average Cost | Simple businesses, fluctuating costs |
| **Specific** | Specific Identification | Unique/high-value items |

### Understanding Each Method

#### FIFO (First In, First Out)

**What it does:** Oldest inventory costs are used first when selling.

**When to use:**
- Perishable goods (matches physical flow)
- Rising prices (lower COGS, higher profit)
- Most common and widely accepted

**Example:**
```
Batch 1: 10 units @ 100 IQD (oldest)
Batch 2: 10 units @ 120 IQD (newest)

Sell 5 units → Cost from Batch 1 = 5 × 100 = 500 IQD
```

#### LIFO (Last In, First Out)

**What it does:** Newest inventory costs are used first when selling.

**When to use:**
- Tax reduction in inflationary periods
- Non-perishable goods only
- Check local tax regulations first

**Example:**
```
Batch 1: 10 units @ 100 IQD (oldest)
Batch 2: 10 units @ 120 IQD (newest)

Sell 5 units → Cost from Batch 2 = 5 × 120 = 600 IQD
```

#### AVCO (Weighted Average Cost)

**What it does:** Average cost across all inventory.

**When to use:**
- Costs fluctuate frequently
- Simple businesses
- Items are interchangeable

**Example:**
```
Batch 1: 10 units @ 100 IQD
Batch 2: 10 units @ 120 IQD

Average = (1000 + 1200) / 20 = 110 IQD per unit
Sell 5 units → Cost = 5 × 110 = 550 IQD
```

#### Specific Identification

**What it does:** Each unit tracked individually with its actual cost.

**When to use:**
- High-value unique items
- Serial-tracked products
- Art, jewelry, vehicles

**Example:**
```
Serial SN-001: Cost 150,000 IQD
Serial SN-002: Cost 165,000 IQD

Sell SN-001 → Cost = 150,000 IQD (exact cost of that unit)
```

### How to Use (UI Steps)

1. Edit the product
2. Select **Costing Method**:
   - FIFO (default)
   - LIFO
   - AVCO
   - Specific (requires serial tracking)
3. Save

For new products, set during creation.

### How to Use (Code)

```php
// Set costing method on product
$product->update(['costing_method' => 'fifo']);
$product->update(['costing_method' => 'lifo']);
$product->update(['costing_method' => 'avco']);
$product->update([
    'costing_method' => 'specific',
    'track_serials' => true, // Required for specific
]);

// Get cost for sale based on product's costing method
$inventoryService = app(InventoryService::class);
$cost = $inventoryService->getCostForRemoval($product, $warehouse);

// Get cost for specific batch
$cost = $inventoryService->getCostForRemoval($product, $warehouse, $batch);
```

### Best Practices

| Do | Don't |
|----|-------|
| Choose method based on business needs | Change methods frequently |
| Consult accountant before choosing | Ignore tax implications |
| Use FIFO for perishables | Use LIFO for expiring products |
| Document costing method in policies | Mix methods randomly |

---

## Feature 7: Reorder Rules

### What Is It?

Reorder rules automatically alert you when stock falls below a threshold, and optionally calculate how much to order. Prevents stockouts of popular items.

### Key Benefits

| Benefit | Description |
|---------|-------------|
| **Prevent Stockouts** | Get alerts before running out |
| **Automation** | Don't manually check every product |
| **Smart Ordering** | Calculate optimal order quantities |
| **Supplier Integration** | Link preferred supplier |
| **Lead Time Planning** | Account for delivery time |

### Key Concepts

| Term | Definition |
|------|------------|
| **Reorder Point** | Stock level that triggers alert |
| **Reorder Quantity** | How much to order |
| **Minimum Quantity** | Never go below this |
| **Maximum Quantity** | Never order above this |
| **Lead Time** | Days for delivery |

### Use Case: Automated Reordering

**Scenario:** Phone store needs to maintain iPhone stock

**Setup:**
- Reorder Point: 5 units (alert when 5 or fewer)
- Reorder Quantity: 20 units
- Minimum: 0
- Maximum: 50
- Lead Time: 7 days
- Preferred Supplier: Apple Distributor

**Operation:**
1. Stock drops to 5 iPhones
2. System generates alert
3. Manager sees suggestion: "Order 20 iPhones from Apple Distributor"
4. Manager creates PO
5. 7 days later, stock replenished

### How to Use (UI Steps)

#### Creating a Reorder Rule

1. Navigate to **Inventory > Reorder Rules** or edit product
2. Click **Create Rule**
3. Set:
   - **Product** and **Warehouse**
   - **Minimum Quantity** (safety stock)
   - **Maximum Quantity** (storage limit)
   - **Reorder Point** (when to alert)
   - **Reorder Quantity** (how much to order)
   - **Lead Time Days** (delivery time)
   - **Preferred Supplier** (optional)
   - **Auto Create PO** (optional automation)
4. Save

#### Viewing Reorder Alerts

1. Dashboard shows reorder widget
2. Or go to **Inventory > Reorder Alerts**
3. See products below reorder point
4. Click to create purchase order

### How to Use (Code)

```php
use App\Models\ReorderRule;

// Create reorder rule
$rule = ReorderRule::create([
    'product_id' => $product->id,
    'warehouse_id' => $warehouse->id,
    'supplier_id' => $preferredSupplier->id,
    'minimum_quantity' => 10,
    'maximum_quantity' => 100,
    'reorder_point' => 25,
    'reorder_quantity' => 50,
    'lead_time_days' => 7,
    'is_active' => true,
    'auto_create_po' => false,
]);

// Check if needs reorder
if ($rule->needsReorder()) {
    $suggestedQty = $rule->getSuggestedOrderQuantity();
    $expectedDelivery = $rule->getExpectedDeliveryDate();
}

// Get all products needing reorder
$needsReorder = ReorderRule::active()
    ->get()
    ->filter(fn($rule) => $rule->needsReorder());
```

---

## Technical Reference

### InventoryService Methods

```php
use App\Services\InventoryService;

$inventoryService = app(InventoryService::class);

// Adding stock
$movement = $inventoryService->addStock(
    product: $product,
    quantity: 100,
    warehouse: $warehouse,
    unitCost: 25.50,
    type: InventoryMovement::TYPE_PURCHASE,
    batch: $batch,
    serialIds: [1, 2, 3],
    location: $location,
    purchase: $purchase,
    reason: 'Purchase receipt'
);

// Removing stock
$movement = $inventoryService->removeStock(
    product: $product,
    quantity: 5,
    warehouse: $warehouse,
    type: InventoryMovement::TYPE_SALE,
    batch: $batch,
    serialIds: [1, 2],
    sale: $sale,
    reason: 'Sale order'
);

// Reserve stock
$success = $inventoryService->reserveStock($product, $warehouse, 10);
$inventoryService->releaseReservedStock($product, $warehouse, 10);

// Stock valuation report
$valuation = $inventoryService->getStockValuation($warehouse);
echo "Total Value: " . $valuation['total_value'];
```

### Database Schema

#### warehouses

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | English name |
| name_ar | varchar | Arabic name |
| name_ckb | varchar | Kurdish name |
| code | varchar | Unique warehouse code |
| address | text | Physical address |
| phone | varchar | Contact phone |
| manager_id | bigint | Manager user FK |
| is_default | boolean | Default warehouse |
| is_active | boolean | Active status |
| allow_negative_stock | boolean | Allow overselling |

#### product_warehouses

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | Product FK |
| warehouse_id | bigint | Warehouse FK |
| quantity | decimal | Current quantity |
| reserved_quantity | decimal | Reserved for orders |
| reorder_point | decimal | Reorder trigger level |
| reorder_quantity | decimal | Amount to reorder |

#### product_batches

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | Product FK |
| warehouse_id | bigint | Warehouse FK |
| batch_number | varchar | Unique batch identifier |
| manufacture_date | date | Manufacturing date |
| expiry_date | date | Expiration date |
| quantity | decimal | Quantity in batch |
| unit_cost | decimal | Cost per unit |
| purchase_id | bigint | Source purchase FK |
| is_active | boolean | Active status |

#### product_serials

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | Product FK |
| warehouse_id | bigint | Current warehouse FK |
| batch_id | bigint | Batch FK (optional) |
| serial_number | varchar | Unique serial number |
| status | enum | available/sold/reserved/damaged/returned |
| cost | decimal | Unit cost |
| purchase_id | bigint | Source purchase FK |
| sale_id | bigint | Sale FK (if sold) |
| warranty_start | date | Warranty start |
| warranty_end | date | Warranty end |

#### stock_transfers

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| transfer_number | varchar | Unique transfer number |
| from_warehouse_id | bigint | Source warehouse |
| to_warehouse_id | bigint | Destination warehouse |
| status | enum | draft/pending/in_transit/partial/completed/cancelled |
| transfer_date | date | Transfer date |
| expected_date | date | Expected arrival |
| received_date | date | Actual receipt |
| created_by | bigint | Creator user FK |
| approved_by | bigint | Approver user FK |
| notes | text | Transfer notes |

#### stock_counts

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| count_number | varchar | Unique count number |
| warehouse_id | bigint | Warehouse FK |
| count_type | enum | full/partial/cycle |
| count_date | date | Count date |
| status | enum | draft/in_progress/completed/cancelled |
| started_at | datetime | Start time |
| completed_at | datetime | Completion time |
| completed_by | bigint | Completer user FK |
| adjustments_posted | boolean | Adjustments applied |
| notes | text | Count notes |

---

## Troubleshooting

### Stock shows negative but shouldn't

**Problem:** Product shows negative stock even with "allow negative" disabled.

**Solutions:**
1. Check pending orders that reserved stock
2. Review inventory movements for errors
3. Run stock count to reconcile

---

### Batch not showing in sale

**Problem:** Can't select batch when selling.

**Solutions:**
1. Verify batch has available quantity
2. Check batch is not expired
3. Ensure batch is in the correct warehouse
4. Verify product has batch tracking enabled

---

### Transfer quantities don't match

**Problem:** Received less than sent.

**Solutions:**
1. Document the variance with notes
2. Complete transfer with actual received quantities
3. Investigate at source warehouse
4. System automatically creates adjustment

---

### Reorder alert not showing

**Problem:** Stock is low but no alert.

**Solutions:**
1. Verify reorder rule is active
2. Check reorder point is set correctly
3. Ensure warehouse matches rule configuration
4. Check reserved quantity vs total quantity

---

*Last updated: January 2026*
