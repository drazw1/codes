<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MANY-TO-MANY relationship pivot table:
     *   A Medicine can be supplied by MANY Suppliers.
     *   A Supplier can supply MANY Medicines.
     *
     *   Extra pivot columns:
     *     - unit_cost   : what the pharmacy pays the supplier per unit
     *     - quantity    : units available from this supplier
     *     - last_supplied_at : date of last delivery
     *
     *   Laravel convention: alphabetical, singular, snake_case → medicine_supplier
     */
    public function up(): void
    {
        Schema::create('medicine_supplier', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('supplier_id');

            // Extra pivot data (not just a join – real information)
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->integer('quantity')->default(0);
            $table->date('last_supplied_at')->nullable();

            $table->timestamps();

            // Composite unique – a supplier can appear once per medicine
            $table->unique(['medicine_id', 'supplier_id']);

            $table->foreign('medicine_id')
                  ->references('medicine_id')
                  ->on('medicines')
                  ->onDelete('cascade');

            $table->foreign('supplier_id')
                  ->references('supplier_id')
                  ->on('suppliers')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_supplier');
    }
};
