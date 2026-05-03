<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory;

    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'email',
        'phone',
        'address',
    ];

    // ── MANY-TO-MANY (inverse side) ──────────────────────────
    /**
     * A Supplier supplies MANY Medicines.
     * Accessed via:  $supplier->medicines
     *
     * withPivot() makes the extra columns available as
     * $medicine->pivot->unit_cost, etc.
     */
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(
                Medicine::class,
                'medicine_supplier',    // pivot table
                'supplier_id',          // FK on pivot pointing to THIS model
                'medicine_id'           // FK on pivot pointing to the OTHER model
            )
            ->withPivot('unit_cost', 'quantity', 'last_supplied_at')
            ->withTimestamps();
    }
}
