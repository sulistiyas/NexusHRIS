<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollBatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'batch_number',
        'month',
        'year',
        'cut_off_start',
        'cut_off_end',
        'payment_date',
        'total_gross',
        'total_deductions',
        'total_net',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'cut_off_start' => 'date',
            'cut_off_end' => 'date',
            'payment_date' => 'date',
            'total_gross' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_net' => 'decimal:2',
        ];
    }

    /**
     * Get the payslips generated in this payroll batch.
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }
}
