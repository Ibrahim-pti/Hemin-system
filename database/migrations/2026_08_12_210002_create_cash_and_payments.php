<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// قاسە (دینار و دۆلار جیا) + حەقدی (وەرگرتن و دانی پارە) + داخستنی ڕۆژانە.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                    // قاسەی دینار / قاسەی دۆلار
            $table->enum('currency', ['IQD', 'USD'])->default('IQD');
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // حەقدی — بەڵگەنامەی پارە کە چاپ دەکرێت.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 30)->unique();

            // in = وەرگرتن لە کڕیار، out = دان بە فرۆشیار/وەستا/خەرجی.
            $table->enum('direction', ['in', 'out']);

            // لایەنی مامەڵە: کڕیار، فرۆشیار، کارمەند — یان ناوێکی ئازاد.
            $table->nullableMorphs('party');
            $table->string('party_name')->nullable();

            // ئەگەر لەسەر حسابی وەسڵێکی دیاریکراو بێت.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 16, 2);
            $table->enum('currency', ['IQD', 'USD'])->default('IQD');
            $table->decimal('exchange_rate', 12, 2)->nullable();
            // بڕەکە بە دینار — بۆ ئەوەی راپۆرت و باڵانس هەمیشە بە یەک دراو بێت.
            $table->decimal('amount_iqd', 16, 2);

            $table->foreignId('cash_box_id')->nullable()->constrained()->nullOnDelete();
            $table->date('paid_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('paid_at');
        });

        // هەموو جوڵەیەکی پارە لە قاسەدا — باڵانسی قاسە لێرەوە دەردەچێت.
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_box_id')->constrained()->restrictOnDelete();
            $table->enum('direction', ['in', 'out']);
            $table->decimal('amount', 16, 2);

            $table->enum('category', [
                'opening',          // باڵانسی سەرەتایی
                'customer_payment', // پارە لە کڕیار
                'supplier_payment', // پارەدان بە فرۆشیار
                'expense',          // خەرجی
                'wage',             // حەقدەستی کارمەند
                'external_job',     // ئیشی خاریجی
                'transfer',         // گواستنەوە نێوان قاسەکان
                'other',
            ]);

            $table->nullableMorphs('reference');
            $table->date('occurred_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['cash_box_id', 'occurred_at']);
        });

        // داخستنی ڕۆژانەی قاسە — بەراوردی باڵانسی سیستەم لەگەڵ ئەوەی ژمێردراو.
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_box_id')->constrained()->restrictOnDelete();
            $table->date('closing_date');
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->decimal('total_in', 16, 2)->default(0);
            $table->decimal('total_out', 16, 2)->default(0);
            $table->decimal('expected_balance', 16, 2)->default(0);
            $table->decimal('counted_balance', 16, 2)->default(0);
            $table->decimal('difference', 16, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['cash_box_id', 'closing_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closings');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('cash_boxes');
    }
};
