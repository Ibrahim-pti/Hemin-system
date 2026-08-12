<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ئیشی خاریجی — ئەو کارەی لە دەرەوەی کارگە دەدرێت (بۆیاخ، خەرات، لەیزەر...).
// دەکرێت بە داواکارییەکەوە ببەسترێت بۆ ئەوەی تێچووی ڕاستەقینەی وەسڵ دەربکەوێت.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no', 30)->unique();
            $table->string('title');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            // یان فرۆشیارێکی تۆمارکراو، یان تەنها ناوێک.
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contractor_name')->nullable();

            $table->text('description')->nullable();
            $table->decimal('cost', 16, 2)->default(0);
            $table->enum('currency', ['IQD', 'USD'])->default('IQD');
            $table->decimal('exchange_rate', 12, 2)->nullable();
            $table->decimal('paid_amount', 16, 2)->default(0);

            $table->enum('status', ['open', 'done', 'cancelled'])->default('open');
            $table->date('started_at')->nullable();
            $table->date('finished_at')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_jobs');
    }
};
