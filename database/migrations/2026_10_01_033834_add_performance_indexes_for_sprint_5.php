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
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('date');
            $table->index('status');
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->index('claim_date');
            $table->index('status');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->index('status');
            $table->index('condition');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('employment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['employment_status']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['condition']);
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropIndex(['claim_date']);
            $table->dropIndex(['status']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['status']);
        });
    }
};
