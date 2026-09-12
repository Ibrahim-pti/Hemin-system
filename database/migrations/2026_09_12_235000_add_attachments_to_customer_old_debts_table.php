<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_old_debts', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('customer_old_debts', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
