<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('fixed_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('meal_allowance', 15, 2)->default(0);
            $table->decimal('bpjs_tk_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_kes_deduction', 15, 2)->default(0);
            $table->decimal('pph21_estimated', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 50)->unique();
            $table->integer('month');
            $table->integer('year');
            $table->date('cut_off_start');
            $table->date('cut_off_end');
            $table->date('payment_date')->nullable();
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->enum('status', ['DRAFT', 'GENERATED', 'APPROVED', 'PAID'])->default('DRAFT');
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained('payroll_batches')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('slip_number', 50)->unique();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('total_overtime_pay', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->string('bank_account_no', 50)->nullable();
            $table->boolean('is_sent_email')->default(false);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained('payslips')->cascadeOnDelete();
            $table->string('component_name', 100);
            $table->enum('component_type', ['EARNING', 'DEDUCTION']);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('request_number', 50)->unique();
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->integer('installment_months')->default(1);
            $table->decimal('monthly_deduction', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'ACTIVE', 'PAID_OFF'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
        Schema::dropIfExists('payslip_items');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_batches');
        Schema::dropIfExists('salary_structures');
    }
};
