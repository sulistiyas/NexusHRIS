<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reimbursement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'category_id',
        'claim_number',
        'claim_date',
        'total_amount',
        'description',
        'status',
        'approved_by',
        'disbursed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'claim_date' => 'date',
            'total_amount' => 'decimal:2',
            'disbursed_at' => 'datetime',
        ];
    }

    /**
     * Get the employee who submitted the claim.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the reimbursement category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ReimbursementCategory::class, 'category_id');
    }

    /**
     * Get the approver of the reimbursement.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get the attachments for this reimbursement claim.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ReimbursementAttachment::class);
    }
}
