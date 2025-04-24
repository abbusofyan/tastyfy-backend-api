<?php

namespace App\Http\Resources;

use App\Enums\DatabaseEnumTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class DatabaseEnumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if (!in_array(DatabaseEnumTrait::class, class_uses_recursive($this->resource))) {
            throw new \InvalidArgumentException('Resource must be an enum that uses the DatabaseEnumTrait.');
        }
        return [
            'value' => $this->resource->value,
            'label' => $this->resource->label(),
        ];
    }
}
