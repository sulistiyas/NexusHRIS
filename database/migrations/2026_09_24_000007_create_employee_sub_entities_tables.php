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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('file_path');
            $table->string('file_name');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('relationship', 50);
            $table->string('phone', 30);
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('career_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type', 50);
            $table->foreignId('previous_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('new_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('previous_designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('new_designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->decimal('previous_salary', 15, 2)->nullable();
            $table->decimal('new_salary', 15, 2)->nullable();
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('warning_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('letter_number', 100)->unique();
            $table->string('warning_level', 20);
            $table->text('reason');
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warning_letters');
        Schema::dropIfExists('career_histories');
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('employee_documents');
    }
};
