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
        Schema::create('reimbursement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('reimbursement_categories')->cascadeOnDelete();
            $table->string('claim_number', 50)->unique();
            $table->date('claim_date');
            $table->decimal('total_amount', 15, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'DISBURSED'])->default('PENDING');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->dateTime('disbursed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reimbursement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reimbursement_id')->constrained('reimbursements')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursement_attachments');
        Schema::dropIfExists('reimbursements');
        Schema::dropIfExists('reimbursement_categories');
    }
};
