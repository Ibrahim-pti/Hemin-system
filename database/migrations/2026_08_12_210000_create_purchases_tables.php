<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// کڕینی مەواد لە فرۆشیارەکانەوە.
// کاتێک پسوولەیەک پەسەند دەکرێت (confirmed) جوڵەی مەخزەنی «چوونەژوورەوە» دروست دەبێت.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->date('purchase_date');

            $table->enum('currency', ['IQD', 'USD'])->default('IQD');
            // نرخی گۆڕین لە کاتی کڕین — بۆ ئەوەی راپۆرتی دینار هەرگیز نەگۆڕێت.
            $table->decimal('exchange_rate', 12, 2)->nullable();

            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('paid_amount', 16, 2)->default(0);

            // draft = هێشتا نەچووەتە مەخزەن. confirmed = چووەتە مەخزەن.
            $table->enum('status', ['draft', 'confirmed'])->default('draft');

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('purchase_date');
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->decimal('qty', 14, 3);
            $table->decimal('unit_price', 16, 2);
            $table->decimal('line_total', 16, 2);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
