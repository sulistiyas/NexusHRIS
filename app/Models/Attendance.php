<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'clock_in',
        'clock_out',
        'in_latitude',
        'in_longitude',
        'out_latitude',
        'out_longitude',
        'in_selfie_path',
        'out_selfie_path',
        'late_minutes',
        'early_leave_minutes',
        'work_type',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'in_latitude' => 'decimal:8',
            'in_longitude' => 'decimal:8',
            'out_latitude' => 'decimal:8',
            'out_longitude' => 'decimal:8',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
        ];
    }

    /**
     * Get the employee who recorded this attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the shift associated with this attendance.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
