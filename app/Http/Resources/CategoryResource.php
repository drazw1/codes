<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->category_id,
            'name'           => $this->category_name,
            'description'    => $this->description,
            'medicines_count'=> $this->whenCounted('medicines'),   // only if withCount() was called
            'medicines'      => MedicineResource::collection($this->whenLoaded('medicines')),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }
}
