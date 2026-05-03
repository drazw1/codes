<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->supplier_id,
            'name'            => $this->supplier_name,
            'contact_person'  => $this->contact_person,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'address'         => $this->address,
            'medicines_count' => $this->whenCounted('medicines'),
            'medicines'       => MedicineResource::collection($this->whenLoaded('medicines')),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
