<?php

namespace App\Http\Resources\V1;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Asset
 */
class AssetResource extends JsonResource
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
            'asset_tag' => $this->asset_tag,
            'name' => $this->name,
            'serial_number' => $this->serial_number,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'purchase_cost' => $this->purchase_cost ? (float) $this->purchase_cost : null,
            'purchase_date' => $this->purchase_date?->format('Y-m-d'),
            'condition' => $this->condition,
            'status' => $this->status,
            'current_assignment' => $this->whenLoaded('assignments', function () {
                $active = $this->assignments->firstWhere('returned_date', null);
                if (! $active) {
                    return null;
                }

                return [
                    'id' => $active->id,
                    'employee_id' => $active->employee_id,
                    'employee_name' => $active->employee?->full_name,
                    'assigned_date' => $active->assigned_date?->format('Y-m-d'),
                    'notes' => $active->notes,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
