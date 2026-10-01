<?php

namespace App\Actions\Reimbursement;

use App\Models\ActivityLog;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DisburseReimbursementAction
{
    /**
     * Disburse an approved reimbursement claim.
     */
    public function execute(Reimbursement $reimbursement, ?int $userId = null, ?string $ip = null, ?string $userAgent = null): Reimbursement
    {
        if ($reimbursement->status !== 'APPROVED') {
            throw new InvalidArgumentException("Hanya klaim berstatus APPROVED yang dapat dicairkan. Status saat ini: {$reimbursement->status}");
        }

        return DB::transaction(function () use ($reimbursement, $userId, $ip, $userAgent): Reimbursement {
            $reimbursement->update([
                'status' => 'DISBURSED',
                'disbursed_at' => now(),
            ]);

            if ($userId) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'event' => 'reimbursement_disbursed',
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'new_values' => [
                        'claim_number' => $reimbursement->claim_number,
                        'total_amount' => $reimbursement->total_amount,
                        'disbursed_at' => $reimbursement->disbursed_at->toDateTimeString(),
                    ],
                ]);
            }

            return $reimbursement;
        });
    }
}
