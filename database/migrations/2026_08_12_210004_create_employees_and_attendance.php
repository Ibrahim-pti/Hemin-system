<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// وەستا و حەمەڵ + تۆماری هاتن و چوون (بە دەستی، ڕۆژانە).
// هەینی بە بنەڕەت پشووە — لە کۆدەکەدا خۆکار وەک 'holiday' دادەنرێت.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->enum('job_title', ['master', 'porter', 'helper', 'driver', 'other'])
                ->default('master');                                    // وەستا / حەمەڵ / یاریدەدەر / شۆفێر
            $table->decimal('daily_wage', 16, 2)->default(0);
            $table->enum('wage_currency', ['IQD', 'USD'])->default('IQD');
            $table->date('hire_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');

            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->enum('status', ['present', 'absent', 'holiday', 'leave'])->default('present');

            $table->decimal('hours', 6, 2)->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            // حەقدەستی ئەو ڕۆژە وەک خۆی تۆمار دەکرێت — چونکە حەقدەست دەگۆڕێت.
            $table->decimal('wage_snapshot', 16, 2)->default(0);

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
            $table->index('work_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
