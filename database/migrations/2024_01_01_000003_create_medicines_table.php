<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ONE-TO-MANY relationship:
     *   A Category has MANY Medicines.
     *   A Medicine belongs to ONE Category.
     *   Enforced here via foreign key: category_id → categories.category_id
     */
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id('medicine_id');
            $table->string('medicine_name', 150);

            // FK for ONE-TO-MANY  (Category → Medicines)
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('set null');

            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->enum('prescription_required', ['YES', 'NO'])->default('NO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
