<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// یەکە، جۆر، و کاڵا (مەواد) — بنەڕەتی مەخزەن.
return new class extends Migration
{
    public function up(): void
    {
        // یەکەکان: دانە، مەتر، مەتر دووجا، کیلۆگرام، لوولە...
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                  // ناوی کوردی
            $table->string('code', 20)->nullable();
            $table->enum('type', ['count', 'length', 'area', 'weight'])->default('count');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();                    // کۆدی کاڵا / بارکۆد
            $table->string('name');
            $table->foreignId('item_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            // ئاگاداری کەمی مەخزەن: کاتێک باڵانس لەمە کەمتر بێت ئاگادار دەکرێتەوە.
            $table->decimal('min_qty', 14, 3)->default(0);

            // دوایین نرخی کڕین — بۆ حیسابی تێچوو. نرخی فرۆشتن پێشنیازکراوە.
            $table->decimal('last_cost', 16, 2)->nullable();
            $table->enum('cost_currency', ['IQD', 'USD'])->default('IQD');
            $table->decimal('sale_price', 16, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_categories');
        Schema::dropIfExists('units');
    }
};
