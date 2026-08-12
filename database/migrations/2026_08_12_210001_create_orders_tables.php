<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// وەسڵی کڕیار (داواکاری) — هەمان پێکهاتەی وەسڵە چاپکراوەکەی کارگە،
// بەڵام لەگەڵ قیاس، داشکاندن، پێشەکی و باڵانسی ماوە.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // ژمارەی وەسڵ — وەک ئەوەی لەسەر دەفتەرە چاپکراوەکە (No. 624).
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();

            $table->date('order_date');
            $table->date('delivery_date')->nullable();

            $table->enum('status', [
                'draft',          // ڕەشنووس
                'confirmed',      // پەسەندکراو
                'in_production',  // لە بەرهەمهێناندا
                'ready',          // ئامادەیە
                'delivered',      // گەیەنرا
                'cancelled',      // هەڵوەشێنراوە
            ])->default('draft');

            $table->enum('currency', ['IQD', 'USD'])->default('IQD');
            $table->decimal('exchange_rate', 12, 2)->nullable();

            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);

            // پێشەکی لە کاتی داواکاری. پارەدانی دواتر لە حەقدییەکانەوە دێت.
            $table->decimal('prepaid_amount', 16, 2)->default(0);

            $table->string('address_snapshot')->nullable(); // ناونیشان وەک لە کاتی وەسڵ
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('order_date');
            $table->index('status');
        });

        // دێڕەکانی وەسڵ — «ناوەڕۆک، ژمارە، نرخ، بڕی پارە» + قیاس.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // ناوەڕۆک: دەقی ئازاد (وەک «دەرگای ئاسنی هەندەسی»).
            $table->string('description');
            // ئەگەر پەیوەندی بە کاڵایەکی مەخزەنەوە هەبێت.
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();

            // شێوازی نرخ: ڕووبەر (م²) / درێژی (م) / دانە.
            $table->enum('pricing_mode', ['area', 'length', 'count'])->default('count');

            // قیاس بە مەتر — بۆ area هەردووکیان، بۆ length تەنها width.
            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();
            $table->decimal('qty', 14, 3)->default(1);

            // بڕی حیسابکراو: area = width×height×qty، length = width×qty، count = qty.
            $table->decimal('computed_qty', 14, 3)->default(0);

            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('line_total', 16, 2)->default(0);

            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
