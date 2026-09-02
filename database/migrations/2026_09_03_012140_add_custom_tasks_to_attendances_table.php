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
            $table->string('custom_task_name')->nullable()->after('trip_destination');
            $table->decimal('custom_task_rate', 16, 2)->default(0)->after('custom_task_name');
            $table->string('custom_task_unit', 20)->default('hourly')->after('custom_task_rate');
            $table->decimal('custom_task_hours', 6, 2)->default(0)->after('custom_task_unit');
            $table->decimal('custom_task_amount', 16, 2)->default(0)->after('custom_task_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'custom_task_name',
                'custom_task_rate',
                'custom_task_unit',
                'custom_task_hours',
                'custom_task_amount',
            ]);
        });
    }
};
