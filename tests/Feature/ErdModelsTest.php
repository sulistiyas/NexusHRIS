<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\CareerHistory;
use App\Models\CashAdvance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeShift;
use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\Overtime;
use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\Permission;
use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use App\Models\ReimbursementCategory;
use App\Models\Role;
use App\Models\SalaryStructure;
use App\Models\Shift;
use App\Models\User;
use App\Models\WarningLetter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErdModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_erd_models_and_relationships_can_be_instantiated_and_persisted(): void
    {
        // 1. User & Roles & Permissions
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'is_active' => true]);

        $role = Role::create(['name' => 'hr_manager', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'manage_employees', 'guard_name' => 'web']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role, ['model_type' => User::class]);

        $this->assertTrue($role->permissions->contains($permission));
        $this->assertTrue($user->roles->contains($role));

        // Activity Log
        $log = ActivityLog::create([
            'user_id' => $user->id,
            'event' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'new_values' => ['action' => 'logged in'],
        ]);
        $this->assertEquals($user->id, $log->user->id);
        $this->assertTrue($user->activityLogs->contains($log));

        // Announcements & Notifications
        $announcement = Announcement::create([
            'user_id' => $user->id,
            'title' => 'Company Townhall',
            'content' => 'Join us this Friday at 4pm.',
            'is_pinned' => true,
            'published_at' => now(),
        ]);
        $this->assertEquals($user->id, $announcement->user->id);

        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Welcome',
            'message' => 'Welcome to NexusHRIS',
            'is_read' => false,
            'data' => ['link' => '/dashboard'],
        ]);
        $this->assertEquals($user->id, $notification->user->id);

        // 2. Organization Structure
        $branch = Branch::create([
            'code' => 'JKT-HQ',
            'name' => 'Jakarta Headquarters',
            'address' => 'Sudirman Central Business District',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 50,
            'timezone' => 'Asia/Jakarta',
        ]);
        $this->assertDatabaseHas('branches', ['code' => 'JKT-HQ']);

        $department = Department::create([
            'branch_id' => $branch->id,
            'code' => 'ENG',
            'name' => 'Engineering',
        ]);
        $this->assertEquals($branch->id, $department->branch->id);

        $designation = Designation::create([
            'department_id' => $department->id,
            'title' => 'Senior Software Engineer',
            'grade_level' => 'L4',
        ]);
        $this->assertEquals($department->id, $designation->department->id);

        // 3. Employee Core
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-2026-001',
            'nik_ktp' => '3171012345678901',
            'npwp' => '12.345.678.9-012.000',
            'bpjs_tk' => '12345678901',
            'bpjs_kes' => '09876543210',
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'gender' => 'MALE',
            'birth_date' => '1995-05-15',
            'birth_place' => 'Jakarta',
            'phone' => '08123456789',
            'employment_status' => 'PKWTT',
            'join_date' => '2026-01-01',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_holder' => 'John Doe',
        ]);
        $this->assertEquals($user->id, $employee->user->id);
        $this->assertEquals($employee->id, $user->employee->id);

        // Update department manager
        $department->update(['manager_id' => $employee->id]);
        $this->assertEquals($employee->id, $department->fresh()->manager->id);

        // 4. Employee Sub-Entities
        $doc = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => 'KTP',
            'file_path' => 'documents/ktp.pdf',
            'file_name' => 'ktp.pdf',
            'expiry_date' => '2030-01-01',
        ]);
        $this->assertEquals($employee->id, $doc->employee->id);

        $contact = EmergencyContact::create([
            'employee_id' => $employee->id,
            'name' => 'Jane Doe',
            'relationship' => 'Spouse',
            'phone' => '08129876543',
            'address' => 'Jakarta',
        ]);
        $this->assertEquals($employee->id, $contact->employee->id);

        $career = CareerHistory::create([
            'employee_id' => $employee->id,
            'type' => 'PROMOTION',
            'previous_department_id' => $department->id,
            'new_department_id' => $department->id,
            'previous_designation_id' => $designation->id,
            'new_designation_id' => $designation->id,
            'previous_salary' => 15000000.00,
            'new_salary' => 20000000.00,
            'effective_date' => '2026-06-01',
            'notes' => 'Promoted to lead engineer',
        ]);
        $this->assertEquals($employee->id, $career->employee->id);

        $warning = WarningLetter::create([
            'employee_id' => $employee->id,
            'letter_number' => 'SP/2026/001',
            'warning_level' => 'SP 1',
            'reason' => 'Late submission',
            'effective_date' => '2026-07-01',
            'issued_by' => $employee->id,
        ]);
        $this->assertEquals($employee->id, $warning->employee->id);

        // 5. Shift & Attendance
        $shift = Shift::create([
            'name' => 'Reguler Office',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'grace_period_minutes' => 15,
            'is_night_shift' => false,
        ]);
        $this->assertDatabaseHas('shifts', ['name' => 'Reguler Office']);

        $empShift = EmployeeShift::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'date' => '2026-09-24',
        ]);
        $this->assertEquals($employee->id, $empShift->employee->id);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'date' => '2026-09-24',
            'clock_in' => '08:55:00',
            'clock_out' => '18:05:00',
            'in_latitude' => -6.2088,
            'in_longitude' => 106.8456,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'work_type' => 'WFO',
            'status' => 'PRESENT',
        ]);
        $this->assertEquals($employee->id, $attendance->employee->id);

        $overtime = Overtime::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-24',
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
            'total_hours' => 2.00,
            'reason' => 'Project deployment',
            'status' => 'APPROVED',
            'approved_by' => $employee->id,
            'approved_at' => now(),
        ]);
        $this->assertEquals($employee->id, $overtime->employee->id);

        // 6. Leave Management
        $leaveType = LeaveType::create([
            'code' => 'ANNUAL',
            'name' => 'Tahunan',
            'default_days_per_year' => 12,
            'is_deduct_annual' => true,
            'requires_attachment' => false,
        ]);

        $leaveBalance = LeaveBalance::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => 2026,
            'total_entitled' => 12,
            'used_days' => 2,
            'remaining_days' => 10,
        ]);
        $this->assertEquals(10, $leaveBalance->remaining_days);

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-03',
            'total_days' => 3,
            'reason' => 'Family vacation',
            'status' => 'PENDING',
        ]);
        $this->assertEquals($employee->id, $leaveRequest->employee->id);

        $approval = LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id' => $employee->id,
            'approval_level' => 1,
            'status' => 'APPROVED',
            'remarks' => 'Approved by manager',
            'acted_at' => now(),
        ]);
        $this->assertEquals($leaveRequest->id, $approval->leaveRequest->id);

        // 7. Payroll Engine
        $salaryStructure = SalaryStructure::create([
            'employee_id' => $employee->id,
            'basic_salary' => 15000000.00,
            'fixed_allowance' => 2000000.00,
            'transport_allowance' => 1000000.00,
            'meal_allowance' => 1000000.00,
            'bpjs_tk_deduction' => 400000.00,
            'bpjs_kes_deduction' => 150000.00,
            'pph21_estimated' => 500000.00,
        ]);
        $this->assertEquals($employee->id, $salaryStructure->employee->id);

        $payrollBatch = PayrollBatch::create([
            'batch_number' => 'BATCH-2026-09',
            'month' => 9,
            'year' => 2026,
            'cut_off_start' => '2026-08-21',
            'cut_off_end' => '2026-09-20',
            'payment_date' => '2026-09-25',
            'total_gross' => 19000000.00,
            'total_deductions' => 1050000.00,
            'total_net' => 17950000.00,
            'status' => 'APPROVED',
        ]);

        $payslip = Payslip::create([
            'payroll_batch_id' => $payrollBatch->id,
            'employee_id' => $employee->id,
            'slip_number' => 'SLIP-202609-001',
            'basic_salary' => 15000000.00,
            'total_allowances' => 4000000.00,
            'total_overtime_pay' => 500000.00,
            'total_deductions' => 1050000.00,
            'net_salary' => 18450000.00,
            'bank_account_no' => '1234567890',
            'is_sent_email' => true,
            'sent_at' => now(),
        ]);
        $this->assertEquals($payrollBatch->id, $payslip->payrollBatch->id);

        $payslipItem = PayslipItem::create([
            'payslip_id' => $payslip->id,
            'component_name' => 'Basic Salary',
            'component_type' => 'EARNING',
            'amount' => 15000000.00,
        ]);
        $this->assertEquals($payslip->id, $payslipItem->payslip->id);

        $cashAdvance = CashAdvance::create([
            'employee_id' => $employee->id,
            'request_number' => 'CA-2026-001',
            'amount' => 5000000.00,
            'reason' => 'Home renovation',
            'installment_months' => 5,
            'monthly_deduction' => 1000000.00,
            'remaining_amount' => 5000000.00,
            'status' => 'ACTIVE',
        ]);
        $this->assertEquals($employee->id, $cashAdvance->employee->id);

        // 8. Reimbursements
        $reimCategory = ReimbursementCategory::create([
            'name' => 'Transport',
            'description' => 'Travel and transport expenses',
        ]);

        $reimbursement = Reimbursement::create([
            'employee_id' => $employee->id,
            'category_id' => $reimCategory->id,
            'claim_number' => 'CLM-2026-001',
            'claim_date' => '2026-09-24',
            'total_amount' => 150000.00,
            'description' => 'Taxi to client meeting',
            'status' => 'APPROVED',
            'approved_by' => $employee->id,
        ]);
        $this->assertEquals($employee->id, $reimbursement->employee->id);

        $attachment = ReimbursementAttachment::create([
            'reimbursement_id' => $reimbursement->id,
            'file_path' => 'reimbursements/receipt.jpg',
            'file_name' => 'receipt.jpg',
        ]);
        $this->assertEquals($reimbursement->id, $attachment->reimbursement->id);

        // 9. Assets
        $assetCategory = AssetCategory::create([
            'name' => 'Electronics',
            'description' => 'Laptops and monitors',
        ]);

        $asset = Asset::create([
            'category_id' => $assetCategory->id,
            'asset_tag' => 'AST-MBP-001',
            'name' => 'MacBook Pro 16 M3 Max',
            'serial_number' => 'SN-12345678',
            'purchase_cost' => 45000000.00,
            'purchase_date' => '2026-01-10',
            'condition' => 'EXCELLENT',
            'status' => 'ASSIGNED',
        ]);
        $this->assertEquals($assetCategory->id, $asset->category->id);

        $assignment = AssetAssignment::create([
            'asset_id' => $asset->id,
            'employee_id' => $employee->id,
            'assigned_date' => '2026-01-15',
            'notes' => 'Handed over in brand new condition',
        ]);
        $this->assertEquals($asset->id, $assignment->asset->id);
        $this->assertEquals($employee->id, $assignment->employee->id);
    }
}
