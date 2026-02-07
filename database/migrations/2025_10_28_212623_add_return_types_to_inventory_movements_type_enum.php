<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN type ENUM('purchase', 'sale', 'damaged', 'destroyed', 'return', 'transfer', 'adjustment', 'sale_return', 'purchase_return') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN type ENUM('purchase', 'sale', 'damaged', 'destroyed', 'return', 'transfer', 'adjustment') NOT NULL");
    }
};
