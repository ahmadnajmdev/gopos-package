<?php

namespace Gopos\Services;

use Gopos\Models\InventoryMovement;
use Gopos\Models\Product;
use Gopos\Models\ProductBatch;
use Gopos\Models\ProductSerial;
use Gopos\Models\ProductWarehouse;
use Gopos\Models\Purchase;
use Gopos\Models\Sale;
use Gopos\Models\StockCount;
use Gopos\Models\StockCountItem;
use Gopos\Models\StockTransfer;
use Gopos\Models\Warehouse;
use Gopos\Models\WarehouseLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock to a warehouse.
     */
    public function addStock(
        Product $product,
        float $quantity,
        ?Warehouse $warehouse = null,
        ?float $unitCost = null,
        ?string $type = InventoryMovement::TYPE_PURCHASE,
        ?ProductBatch $batch = null,
        ?array $serialIds = null,
        ?WarehouseLocation $location = null,
        ?Purchase $purchase = null,
        ?string $reason = null
    ): InventoryMovement {
        $warehouse = $warehouse ?? Warehouse::getDefault();

        return DB::transaction(function () use ($product, $quantity, $warehouse, $unitCost, $type, $batch, $serialIds, $location, $purchase, $reason) {
            // Create movement record
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse?->id,
                'location_id' => $location?->id,
                'batch_id' => $batch?->id,
                'serial_ids' => $serialIds,
                'type' => $type,
                'quantity' => abs($quantity),
                'unit_cost' => $unitCost,
                'purchase_id' => $purchase?->id,
                'user_id' => auth()->id(),
                'reason' => $reason,
                'movement_date' => now(),
            ]);

            // Update warehouse stock
            if ($warehouse) {
                $this->updateWarehouseStock($product, $warehouse, abs($quantity));
            }

            // Update batch quantity if applicable
            if ($batch) {
                $batch->increment('quantity', abs($quantity));
            }

            // Update serial statuses if applicable
            if ($serialIds) {
                ProductSerial::whereIn('id', $serialIds)->update([
                    'status' => ProductSerial::STATUS_AVAILABLE,
                    'warehouse_id' => $warehouse?->id,
                ]);
            }

            // Update average cost
            $this->updateAverageCost($product, abs($quantity), $unitCost ?? 0);

            return $movement;
        });
    }

    /**
     * Remove stock from a warehouse.
     */
    public function removeStock(
        Product $product,
        float $quantity,
        ?Warehouse $warehouse = null,
        ?string $type = InventoryMovement::TYPE_SALE,
        ?ProductBatch $batch = null,
        ?array $serialIds = null,
        ?Sale $sale = null,
        ?string $reason = null
    ): InventoryMovement {
        $warehouse = $warehouse ?? Warehouse::getDefault();
        $unitCost = $this->getCostForRemoval($product, $warehouse, $batch);

        return DB::transaction(function () use ($product, $quantity, $warehouse, $unitCost, $type, $batch, $serialIds, $sale, $reason) {
            // Create movement record (negative quantity)
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse?->id,
                'batch_id' => $batch?->id,
                'serial_ids' => $serialIds,
                'type' => $type,
                'quantity' => -abs($quantity),
                'unit_cost' => $unitCost,
                'sale_id' => $sale?->id,
                'user_id' => auth()->id(),
                'reason' => $reason,
                'movement_date' => now(),
            ]);

            // Update warehouse stock
            if ($warehouse) {
                $this->updateWarehouseStock($product, $warehouse, -abs($quantity));
            }

            // Update batch quantity if applicable
            if ($batch) {
                $batch->decrement('quantity', abs($quantity));
            }

            // Update serial statuses if applicable
            if ($serialIds && $sale) {
                ProductSerial::whereIn('id', $serialIds)->update([
                    'status' => ProductSerial::STATUS_SOLD,
                    'sale_id' => $sale->id,
                ]);
            }

            return $movement;
        });
    }

    /**
     * Transfer stock between warehouses.
     */
    public function transferStock(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Remove from source warehouse
                $this->removeStock(
                    $item->product,
                    $item->quantity_sent,
                    $transfer->fromWarehouse,
                    InventoryMovement::TYPE_TRANSFER_OUT,
                    $item->batch,
                    $item->serial_ids,
                    null,
                    "Transfer to {$transfer->toWarehouse->name}"
                );

                // Add to destination warehouse
                $this->addStock(
                    $item->product,
                    $item->quantity_sent,
                    $transfer->toWarehouse,
                    $item->unit_cost,
                    InventoryMovement::TYPE_TRANSFER_IN,
                    $item->batch,
                    $item->serial_ids,
                    null,
                    null,
                    "Transfer from {$transfer->fromWarehouse->name}"
                );

                // Update serials warehouse
                if ($item->serial_ids) {
                    ProductSerial::whereIn('id', $item->serial_ids)->update([
                        'warehouse_id' => $transfer->to_warehouse_id,
                    ]);
                }

                // Update batch warehouse if needed
                if ($item->batch) {
                    // Create a new batch in destination or move
                    // For simplicity, we'll just track the movement
                }
            }

            $transfer->update(['status' => StockTransfer::STATUS_COMPLETED]);
        });
    }

    /**
     * Receive transfer items.
     */
    public function receiveTransferItems(StockTransfer $transfer, array $receivedQuantities): void
    {
        DB::transaction(function () use ($transfer, $receivedQuantities) {
            $allFullyReceived = true;

            foreach ($receivedQuantities as $itemId => $quantityReceived) {
                $item = $transfer->items()->find($itemId);
                if (! $item) {
                    continue;
                }

                $item->update(['quantity_received' => $quantityReceived]);

                if ($quantityReceived < $item->quantity_sent) {
                    $allFullyReceived = false;
                }
            }

            $transfer->update([
                'status' => $allFullyReceived ? StockTransfer::STATUS_COMPLETED : StockTransfer::STATUS_PARTIAL,
                'received_date' => now(),
                'received_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Post stock count adjustments.
     */
    public function postStockCountAdjustments(StockCount $stockCount): void
    {
        DB::transaction(function () use ($stockCount) {
            foreach ($stockCount->items()->whereNotNull('counted_quantity')->get() as $item) {
                if ($item->variance == 0) {
                    $item->update(['status' => StockCountItem::STATUS_ADJUSTED]);

                    continue;
                }

                // Create adjustment movement
                $type = InventoryMovement::TYPE_COUNT_ADJUSTMENT;

                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $stockCount->warehouse_id,
                    'location_id' => $item->location_id,
                    'batch_id' => $item->batch_id,
                    'type' => $type,
                    'quantity' => $item->variance,
                    'unit_cost' => $item->unit_cost,
                    'user_id' => auth()->id(),
                    'reason' => "Stock count adjustment: {$stockCount->count_number}",
                    'movement_date' => now(),
                ]);

                // Update warehouse stock
                $this->updateWarehouseStock(
                    $item->product,
                    $stockCount->warehouse,
                    $item->variance
                );

                $item->update(['status' => StockCountItem::STATUS_ADJUSTED]);
            }

            $stockCount->update(['adjustments_posted' => true]);
        });
    }

    /**
     * Get cost for stock removal based on costing method.
     */
    public function getCostForRemoval(Product $product, ?Warehouse $warehouse = null, ?ProductBatch $batch = null): float
    {
        // If specific batch provided, use its cost
        if ($batch) {
            return $batch->unit_cost;
        }

        $costingMethod = $product->costing_method ?? 'avco';

        return match ($costingMethod) {
            'fifo' => $this->getFifoCost($product, $warehouse),
            'lifo' => $this->getLifoCost($product, $warehouse),
            'specific' => $product->average_cost,
            default => $product->average_cost, // avco
        };
    }

    /**
     * Get FIFO cost (oldest batch first).
     */
    protected function getFifoCost(Product $product, ?Warehouse $warehouse = null): float
    {
        $query = $product->batches()->active()->withStock()->fifo();

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        $batch = $query->first();

        return $batch ? $batch->unit_cost : $product->average_cost;
    }

    /**
     * Get LIFO cost (newest batch first).
     */
    protected function getLifoCost(Product $product, ?Warehouse $warehouse = null): float
    {
        $query = $product->batches()->active()->withStock()->lifo();

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        $batch = $query->first();

        return $batch ? $batch->unit_cost : $product->average_cost;
    }

    /**
     * Update average cost using weighted average method.
     */
    protected function updateAverageCost(Product $product, float $quantity, float $unitCost): void
    {
        $currentStock = max(0, $product->stock - $quantity);
        $currentValue = $currentStock * $product->average_cost;
        $newValue = $quantity * $unitCost;
        $totalStock = $currentStock + $quantity;

        if ($totalStock > 0) {
            $newAverage = ($currentValue + $newValue) / $totalStock;
            $product->update(['average_cost' => $newAverage]);
        }
    }

    /**
     * Update warehouse stock quantity.
     */
    protected function updateWarehouseStock(Product $product, Warehouse $warehouse, float $quantity): void
    {
        $productWarehouse = ProductWarehouse::firstOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
            ]
        );

        $productWarehouse->increment('quantity', $quantity);
    }

    /**
     * Reserve stock for a pending order.
     */
    public function reserveStock(Product $product, Warehouse $warehouse, float $quantity): bool
    {
        $productWarehouse = ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        if (! $productWarehouse) {
            return false;
        }

        $available = $productWarehouse->quantity - $productWarehouse->reserved_quantity;

        if ($available < $quantity) {
            return false;
        }

        $productWarehouse->increment('reserved_quantity', $quantity);

        return true;
    }

    /**
     * Release reserved stock.
     */
    public function releaseReservedStock(Product $product, Warehouse $warehouse, float $quantity): void
    {
        ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->decrement('reserved_quantity', $quantity);
    }

    /**
     * Get products needing reorder.
     */
    public function getProductsNeedingReorder(?Warehouse $warehouse = null): Collection
    {
        $query = ProductWarehouse::with(['product', 'warehouse'])
            ->whereNotNull('reorder_point')
            ->whereColumn('quantity', '<=', 'reorder_point');

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->get();
    }

    /**
     * Get expiring batches.
     */
    public function getExpiringBatches(int $days = 30, ?Warehouse $warehouse = null): Collection
    {
        $query = ProductBatch::with(['product', 'warehouse'])
            ->active()
            ->withStock()
            ->expiringSoon($days);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->orderBy('expiry_date')->get();
    }

    /**
     * Get expired batches.
     */
    public function getExpiredBatches(?Warehouse $warehouse = null): Collection
    {
        $query = ProductBatch::with(['product', 'warehouse'])
            ->active()
            ->withStock()
            ->expired();

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->orderBy('expiry_date')->get();
    }

    /**
     * Create a batch for a product.
     */
    public function createBatch(
        Product $product,
        string $batchNumber,
        float $quantity,
        float $unitCost,
        ?Warehouse $warehouse = null,
        ?\DateTimeInterface $manufactureDate = null,
        ?\DateTimeInterface $expiryDate = null,
        ?Purchase $purchase = null
    ): ProductBatch {
        return ProductBatch::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse?->id,
            'batch_number' => $batchNumber,
            'manufacture_date' => $manufactureDate,
            'expiry_date' => $expiryDate,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'purchase_id' => $purchase?->id,
        ]);
    }

    /**
     * Create serial numbers for a product.
     */
    public function createSerials(
        Product $product,
        array $serialNumbers,
        float $cost,
        ?Warehouse $warehouse = null,
        ?ProductBatch $batch = null,
        ?Purchase $purchase = null
    ): Collection {
        $serials = collect();
        $warrantyMonths = $product->warranty_months;

        foreach ($serialNumbers as $serialNumber) {
            $serial = ProductSerial::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse?->id,
                'batch_id' => $batch?->id,
                'serial_number' => $serialNumber,
                'status' => ProductSerial::STATUS_AVAILABLE,
                'cost' => $cost,
                'purchase_id' => $purchase?->id,
                'warranty_start' => $warrantyMonths ? now() : null,
                'warranty_end' => $warrantyMonths ? now()->addMonths($warrantyMonths) : null,
            ]);
            $serials->push($serial);
        }

        return $serials;
    }

    /**
     * Get stock valuation report.
     */
    public function getStockValuation(?Warehouse $warehouse = null): array
    {
        $query = ProductWarehouse::with(['product', 'warehouse'])
            ->where('quantity', '>', 0);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        $items = $query->get();

        $totalValue = 0;
        $valuation = [];

        foreach ($items as $item) {
            $value = $item->quantity * ($item->product->average_cost ?? 0);
            $totalValue += $value;

            $valuation[] = [
                'product' => $item->product,
                'warehouse' => $item->warehouse,
                'quantity' => $item->quantity,
                'unit_cost' => $item->product->average_cost ?? 0,
                'total_value' => $value,
            ];
        }

        return [
            'items' => $valuation,
            'total_value' => $totalValue,
        ];
    }

    /**
     * Initialize stock count items for a warehouse.
     */
    public function initializeStockCount(StockCount $stockCount, ?array $productIds = null): void
    {
        $query = ProductWarehouse::where('warehouse_id', $stockCount->warehouse_id);

        if ($productIds) {
            $query->whereIn('product_id', $productIds);
        }

        $productWarehouses = $query->get();

        foreach ($productWarehouses as $pw) {
            StockCountItem::create([
                'stock_count_id' => $stockCount->id,
                'product_id' => $pw->product_id,
                'system_quantity' => $pw->quantity,
                'unit_cost' => $pw->product->average_cost ?? 0,
                'status' => StockCountItem::STATUS_PENDING,
            ]);
        }
    }
}
