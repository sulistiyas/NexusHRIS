<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payroll_batch_id',
        'employee_id',
        'slip_number',
        'basic_salary',
        'total_allowances',
        'total_overtime_pay',
        'total_deductions',
        'net_salary',
        'bank_account_no',
        'is_sent_email',
        'sent_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'total_overtime_pay' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'is_sent_email' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the payroll batch that contains this payslip.
     */
    public function payrollBatch(): BelongsTo
    {
        return $this->belongsTo(PayrollBatch::class);
    }

    /**
     * Get the employee who receives this payslip.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the line items on this payslip.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }
}
