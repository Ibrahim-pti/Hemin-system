<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// کڕیار (ناو + تەلەفۆن + شوێن) و فرۆشیار (ئەو شوێنانەی مەواد لێ دەکڕین).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('phone2', 30)->nullable();
            $table->string('address')->nullable();               // شوێن

            // داشکاندنی هەمیشەیی — بە بنەڕەت لە وەسڵی نوێدا دادەنرێت.
            $table->decimal('discount_percent', 5, 2)->default(0);

            // باڵانسی سەرەتایی: ئەرێنی = قەرزاری کارگەیە.
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->enum('opening_currency', ['IQD', 'USD'])->default('IQD');

            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('phone');
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('phone2', 30)->nullable();
            $table->string('address')->nullable();

            // ئەرێنی = کارگە قەرزاری ئەم فرۆشیارەیە.
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->enum('opening_currency', ['IQD', 'USD'])->default('IQD');

            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
