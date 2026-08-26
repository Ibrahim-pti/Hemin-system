<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('temporary_exit_hours', 6, 2)->default(0)->after('overtime_hours');
            $table->string('exit_reason')->nullable()->after('temporary_exit_hours');
            $table->decimal('fuel_expense', 16, 2)->default(0)->after('exit_reason');
            $table->string('trip_destination')->nullable()->after('fuel_expense');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'temporary_exit_hours',
                'exit_reason',
                'fuel_expense',
                'trip_destination',
            ]);
        });
    }
};
