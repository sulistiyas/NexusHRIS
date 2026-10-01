<?php

namespace App\Actions\Reimbursement;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubmitReimbursementAction
{
    /**
     * Execute the reimbursement submission.
     *
     * @param  array{category_id: int, claim_date: string, total_amount: float|int|string, description?: ?string}  $data
     * @param  list<UploadedFile>|UploadedFile|null  $files
     */
    public function execute(Employee $employee, array $data, array|UploadedFile|null $files = null, ?int $userId = null, ?string $ip = null, ?string $userAgent = null): Reimbursement
    {
        return DB::transaction(function () use ($employee, $data, $files, $userId, $ip, $userAgent): Reimbursement {
            $datePrefix = now()->format('Ymd');
            $uniqueSuffix = strtoupper(Str::random(4));
            $claimNumber = "REIMB-{$datePrefix}-{$uniqueSuffix}";

            while (Reimbursement::where('claim_number', $claimNumber)->exists()) {
                $uniqueSuffix = strtoupper(Str::random(4));
                $claimNumber = "REIMB-{$datePrefix}-{$uniqueSuffix}";
            }

            $reimbursement = Reimbursement::create([
                'employee_id' => $employee->id,
                'category_id' => (int) $data['category_id'],
                'claim_number' => $claimNumber,
                'claim_date' => $data['claim_date'],
                'total_amount' => $data['total_amount'],
                'description' => $data['description'] ?? null,
                'status' => 'PENDING',
                'approved_by' => null,
                'disbursed_at' => null,
            ]);

            if ($files) {
                $fileList = is_array($files) ? $files : [$files];

                foreach ($fileList as $file) {
                    if ($file instanceof UploadedFile) {
                        $storedPath = $file->store('reimbursements', 'private');

                        ReimbursementAttachment::create([
                            'reimbursement_id' => $reimbursement->id,
                            'file_path' => $storedPath,
                            'file_name' => $file->getClientOriginalName(),
                        ]);
                    }
                }
            }

            if ($userId) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'event' => 'reimbursement_submitted',
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'new_values' => [
                        'claim_number' => $reimbursement->claim_number,
                        'total_amount' => $reimbursement->total_amount,
                        'employee_id' => $employee->id,
                    ],
                ]);
            }

            return $reimbursement;
        });
    }
}
