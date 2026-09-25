<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'is_night_shift',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grace_period_minutes' => 'integer',
            'is_night_shift' => 'boolean',
        ];
    }

    /**
     * Get the employee shifts scheduled for this shift.
     */
    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    /**
     * Get attendances recorded under this shift.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
