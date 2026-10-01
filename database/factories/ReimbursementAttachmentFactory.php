<?php

namespace Database\Factories;

use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReimbursementAttachment>
 */
class ReimbursementAttachmentFactory extends Factory
{
    protected $model = ReimbursementAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reimbursement_id' => Reimbursement::factory(),
            'file_path' => 'reimbursements/receipt_'.fake()->uuid().'.jpg',
            'file_name' => 'receipt_'.fake()->word().'.jpg',
        ];
    }
}
