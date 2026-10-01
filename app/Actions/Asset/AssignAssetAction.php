<?php

namespace App\Actions\Asset;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssignAssetAction
{
    /**
     * Assign an available asset to an employee.
     *
     * @param  array{assigned_date: string, notes?: ?string}  $data
     */
    public function execute(Asset $asset, Employee $employee, array $data, ?int $userId = null, ?string $ip = null, ?string $userAgent = null): AssetAssignment
    {
        if ($asset->status !== 'AVAILABLE') {
            throw new InvalidArgumentException("Aset '{$asset->name}' tidak tersedia untuk diserahkan. Status saat ini: {$asset->status}");
        }

        return DB::transaction(function () use ($asset, $employee, $data, $userId, $ip, $userAgent): AssetAssignment {
            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id,
                'employee_id' => $employee->id,
                'assigned_date' => $data['assigned_date'] ?? now()->format('Y-m-d'),
                'notes' => $data['notes'] ?? null,
            ]);

            $asset->update([
                'status' => 'ASSIGNED',
            ]);

            if ($userId) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'event' => 'asset_assigned',
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'new_values' => [
                        'asset_id' => $asset->id,
                        'asset_tag' => $asset->asset_tag,
                        'employee_id' => $employee->id,
                        'assigned_date' => $assignment->assigned_date->format('Y-m-d'),
                    ],
                ]);
            }

            return $assignment;
        });
    }
}
