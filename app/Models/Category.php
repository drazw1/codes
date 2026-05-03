<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    // Non-default primary key name
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'description',
    ];

    // ── ONE-TO-MANY ──────────────────────────────────────────
    /**
     * A Category HAS MANY Medicines.
     * Accessed via:  $category->medicines
     *
     * Eloquent uses the FK 'category_id' on the medicines table
     * to join – matching our migration definition.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'category_id', 'category_id');
    }
}
