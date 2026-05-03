<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MedicineResource
 * ─────────────────
 * Transforms a single Medicine Eloquent model into a clean JSON structure.
 * Used by the API controllers to ensure consistent output shape.
 *
 * Usage:
 *   return new MedicineResource($medicine);
 *   return MedicineResource::collection($medicines);
 */
class MedicineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->medicine_id,
            'name'                  => $this->medicine_name,
            'price'                 => (float) $this->price,
            'stock'                 => (int)   $this->stock,
            'stock_status'          => $this->stockStatus(),
            'prescription_required' => $this->prescription_required,

            // ONE-TO-MANY: include category details when loaded
            'category' => $this->whenLoaded('category', fn() => [
                'id'   => $this->category?->category_id,
                'name' => $this->category?->category_name,
            ]),

            // MANY-TO-MANY: include suppliers array with pivot data
            'suppliers' => $this->whenLoaded('suppliers', fn() =>
                $this->suppliers->map(fn($sup) => [
                    'id'               => $sup->supplier_id,
                    'name'             => $sup->supplier_name,
                    'email'            => $sup->email,
                    // pivot columns accessed via ->pivot
                    'unit_cost'        => (float) $sup->pivot->unit_cost,
                    'quantity'         => (int)   $sup->pivot->quantity,
                    'last_supplied_at' => $sup->pivot->last_supplied_at,
                ])
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function stockStatus(): string
    {
        return match(true) {
            $this->stock === 0   => 'out_of_stock',
            $this->stock < 10    => 'low_stock',
            $this->stock < 50    => 'moderate',
            default              => 'well_stocked',
        };
    }
}
