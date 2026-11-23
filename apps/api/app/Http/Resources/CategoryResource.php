<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Prevodi model Kategorija (hrvatski stupci) u API oblik iz OpenAPI-ja (schema Category).
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->naziv,
            'max_age' => $this->max_dob,   // null = seniori (bez gornje granice)
        ];
    }
}
