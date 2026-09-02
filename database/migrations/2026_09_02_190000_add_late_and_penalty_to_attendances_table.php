<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status', 50)->default('present')->change();
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->integer('late_minutes')->default(0)->after('overtime_hours');
            }
            if (!Schema::hasColumn('attendances', 'deduction_amount')) {
                $table->decimal('deduction_amount', 16, 2)->default(0)->after('fuel_expense');
            }
            if (!Schema::hasColumn('attendances', 'bonus_amount')) {
                $table->decimal('bonus_amount', 16, 2)->default(0)->after('deduction_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['late_minutes', 'deduction_amount', 'bonus_amount']);
        });
    }
};
