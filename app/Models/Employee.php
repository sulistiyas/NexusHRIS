<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_code',
        'nik_ktp',
        'npwp',
        'bpjs_tk',
        'bpjs_kes',
        'branch_id',
        'department_id',
        'designation_id',
        'manager_id',
        'gender',
        'birth_date',
        'birth_place',
        'phone',
        'employment_status',
        'join_date',
        'contract_end_date',
        'bank_name',
        'bank_account_no',
        'bank_account_holder',
        'avatar_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'join_date' => 'date',
            'contract_end_date' => 'date',
        ];
    }

    /**
     * Get the user account for the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where the employee is assigned.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the department where the employee is assigned.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designation of the employee.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get the manager of the employee.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /**
     * Get the subordinates managed by the employee.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    /**
     * Get documents uploaded for the employee.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get emergency contacts for the employee.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get career history records for the employee.
     */
    public function careerHistories(): HasMany
    {
        return $this->hasMany(CareerHistory::class);
    }

    /**
     * Get warning letters received by the employee.
     */
    public function warningLetters(): HasMany
    {
        return $this->hasMany(WarningLetter::class);
    }

    /**
     * Get shifts scheduled for the employee.
     */
    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    /**
     * Get attendances recorded by the employee.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get overtime requests made by the employee.
     */
    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    /**
     * Get leave balances for the employee.
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get leave requests made by the employee.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the configured salary structure for the employee.
     */
    public function salaryStructure(): HasOne
    {
        return $this->hasOne(SalaryStructure::class);
    }

    /**
     * Get payslips generated for the employee.
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    /**
     * Get cash advance requests by the employee.
     */
    public function cashAdvances(): HasMany
    {
        return $this->hasMany(CashAdvance::class);
    }

    /**
     * Get reimbursement claims submitted by the employee.
     */
    public function reimbursements(): HasMany
    {
        return $this->hasMany(Reimbursement::class);
    }

    /**
     * Get asset assignments for the employee.
     */
    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }
}
