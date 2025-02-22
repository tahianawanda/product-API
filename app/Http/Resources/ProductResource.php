<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'products',
            'attributes' => [
                'name' => $this->name,
                'price' => $this->price,
                'description' => $this->description,
                'stock' => $this->stock,
            ],
            'links' => [
                'self' => url('/api/products/' . $this->resource->getRouteKey())
            ]

        ];
    }
}
