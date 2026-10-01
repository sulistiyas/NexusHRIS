<?php

namespace App\Actions\Asset;

use App\Models\ActivityLog;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReturnAssetAction
{
    /**
     * Process return of an assigned asset.
     *
     * @param  array{returned_date: string, return_condition: string, notes?: ?string}  $data
     */
    public function execute(AssetAssignment $assignment, array $data, ?int $userId = null, ?string $ip = null, ?string $userAgent = null): AssetAssignment
    {
        if ($assignment->returned_date !== null) {
            throw new InvalidArgumentException("Aset ini telah dikembalikan pada tanggal {$assignment->returned_date->format('Y-m-d')}.");
        }

        return DB::transaction(function () use ($assignment, $data, $userId, $ip, $userAgent): AssetAssignment {
            $returnCondition = $data['return_condition'] ?? 'GOOD';

            $assignment->update([
                'returned_date' => $data['returned_date'] ?? now()->format('Y-m-d'),
                'return_condition' => $returnCondition,
                'notes' => $data['notes'] ?? $assignment->notes,
            ]);

            $asset = $assignment->asset;
            $newStatus = $returnCondition === 'DAMAGED' ? 'UNDER_MAINTENANCE' : 'AVAILABLE';

            $asset->update([
                'condition' => $returnCondition,
                'status' => $newStatus,
            ]);

            if ($userId) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'event' => 'asset_returned',
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'new_values' => [
                        'asset_id' => $asset->id,
                        'asset_tag' => $asset->asset_tag,
                        'return_condition' => $returnCondition,
                        'new_status' => $newStatus,
                        'returned_date' => $assignment->returned_date->format('Y-m-d'),
                    ],
                ]);
            }

            return $assignment;
        });
    }
}
