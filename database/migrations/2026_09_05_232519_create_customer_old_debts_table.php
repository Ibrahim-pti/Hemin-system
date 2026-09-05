<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_old_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 2);
            $table->decimal('paid_amount', 16, 2)->default(0);
            $table->enum('currency', ['IQD', 'USD'])->default('IQD');
            $table->enum('status', ['debt', 'paid', 'partial'])->default('debt');
            $table->string('image')->nullable();
            $table->text('note')->nullable();
            $table->date('date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_old_debts');
    }
};
