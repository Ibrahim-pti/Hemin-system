<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('discount', 16, 2)->default(0)->after('amount');
            $table->decimal('discount_iqd', 16, 2)->default(0)->after('amount_iqd');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['discount', 'discount_iqd']);
        });
    }
};
