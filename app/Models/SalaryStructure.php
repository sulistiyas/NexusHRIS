<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructure extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'fixed_allowance',
        'transport_allowance',
        'meal_allowance',
        'bpjs_tk_deduction',
        'bpjs_kes_deduction',
        'pph21_estimated',
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
            'fixed_allowance' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'meal_allowance' => 'decimal:2',
            'bpjs_tk_deduction' => 'decimal:2',
            'bpjs_kes_deduction' => 'decimal:2',
            'pph21_estimated' => 'decimal:2',
        ];
    }

    /**
     * Get the employee associated with the salary structure.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
