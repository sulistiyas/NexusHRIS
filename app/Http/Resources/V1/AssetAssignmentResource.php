<?php

namespace App\Http\Resources\V1;

use App\Models\AssetAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AssetAssignment
 */
class AssetAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'assigned_date' => $this->assigned_date?->format('Y-m-d'),
            'returned_date' => $this->returned_date?->format('Y-m-d'),
            'return_condition' => $this->return_condition,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
