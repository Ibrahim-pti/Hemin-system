<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// نرخی گۆڕینی دۆلار بۆ دینار بۆ هەر ڕۆژێک.
// دینار دراوی بنەڕەتە؛ هەر مامەڵەیەکی دۆلاری نرخی ئەو ڕۆژەی لەگەڵدا تۆمار دەکرێت.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->date('effective_date')->unique();
            $table->decimal('usd_to_iqd', 12, 2);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
