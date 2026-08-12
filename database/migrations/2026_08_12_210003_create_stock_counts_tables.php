<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// جەردی کۆگا — بەراوردی ژمارەی سیستەم لەگەڵ ژمارەی ڕاستەقینە.
// کاتێک پەسەند دەکرێت، جیاوازییەکان دەبنە جوڵەی «ڕاستکردنەوە».
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->string('count_no', 30)->unique();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->date('count_date');
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            // ژمارەی سیستەم لە کاتی دەستپێکردنی جەرد — دوایی ناگۆڕێت.
            $table->decimal('system_qty', 14, 3)->default(0);
            $table->decimal('counted_qty', 14, 3)->nullable();
            $table->decimal('difference', 14, 3)->default(0);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['stock_count_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
    }
};
