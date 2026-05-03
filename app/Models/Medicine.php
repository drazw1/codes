<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medicine extends Model
{
    use HasFactory;

    protected $primaryKey = 'medicine_id';

    protected $fillable = [
        'medicine_name',
        'category_id',
        'price',
        'stock',
        'prescription_required',
    ];

    protected $casts = [
        'price'   => 'float',
        'stock'   => 'integer',
        'category_id' => 'integer',
    ];

    // ── ONE-TO-MANY (inverse / belongs-to side) ───────────────
    /**
     * A Medicine BELONGS TO one Category.
     * Accessed via:  $medicine->category
     *
     * Eloquent looks for 'category_id' on this model's table
     * and joins to categories.category_id.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // ── MANY-TO-MANY ─────────────────────────────────────────
    /**
     * A Medicine is supplied by MANY Suppliers.
     * Accessed via:  $medicine->suppliers
     *
     * The pivot table 'medicine_supplier' holds extra columns:
     *   unit_cost, quantity, last_supplied_at
     *
     * Access them via:  $supplier->pivot->unit_cost
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(
                Supplier::class,
                'medicine_supplier',    // pivot table name
                'medicine_id',          // FK pointing to THIS model
                'supplier_id'           // FK pointing to the OTHER model
            )
            ->withPivot('unit_cost', 'quantity', 'last_supplied_at')
            ->withTimestamps();
    }

    // ── Helper scopes ─────────────────────────────────────────

    /** Scope: only low-stock medicines (stock < 10) */
    public function scopeLowStock($query)
    {
        return $query->where('stock', '<', 10);
    }

    /** Scope: prescription-only medicines */
    public function scopeRxOnly($query)
    {
        return $query->where('prescription_required', 'YES');
    }
}
