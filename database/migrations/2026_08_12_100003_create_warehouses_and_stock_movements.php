<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// کۆگا و جوڵەی مەخزەن.
//
// گرنگ: باڵانسی هیچ کاڵایەک لە جێگایەکدا هەڵناگیرێت — هەمیشە لە کۆکردنەوەی
// stock_movements دەردەچێت. بەم شێوەیە مێژووی تەواو دەمێنێتەوە و باڵانس
// هەرگیز لەگەڵ مامەڵەکان ناتەبا نابێت.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();

            // in = چوونەژوورەوە، out = دەرچوون. بڕ هەمیشە ئەرێنییە.
            $table->enum('direction', ['in', 'out']);
            $table->decimal('qty', 14, 3);

            // هۆکار — دیاری دەکات جوڵەکە لە کوێوە هاتووە.
            $table->enum('reason', [
                'opening',          // باڵانسی سەرەتایی
                'purchase',         // کڕین
                'purchase_return',  // گەڕاندنەوەی کڕین
                'sale',             // فرۆشتن
                'sale_return',      // گەڕاندنەوەی فرۆشتن
                'transfer',         // گواستنەوە نێوان کۆگاکان
                'adjustment',       // ڕاستکردنەوەی جەرد
                'production',       // بەکارهێنان لە بەرهەمهێنان
                'damage',           // تێکچوون / زیان
            ]);

            // تێچووی یەکە لە کاتی جوڵەکە (بۆ کڕین) — بۆ حیسابی قازانج.
            $table->decimal('unit_cost', 16, 2)->nullable();
            $table->enum('currency', ['IQD', 'USD'])->default('IQD');
            $table->decimal('exchange_rate', 12, 2)->nullable();

            // پەیوەندی بە بەڵگەنامەکەوە (پسوولەی کڕین، وەسڵ، جەرد...).
            $table->nullableMorphs('reference');

            $table->date('moved_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'warehouse_id']);
            $table->index('moved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('warehouses');
    }
};
