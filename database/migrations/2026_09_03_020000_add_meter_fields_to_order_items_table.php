<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'meter')) {
                $table->decimal('meter', 10, 3)->nullable()->after('pricing_mode');
            }
            if (! Schema::hasColumn('order_items', 'meter_price')) {
                $table->decimal('meter_price', 16, 2)->nullable()->after('meter');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'meter_price')) {
                $table->dropColumn('meter_price');
            }
            if (Schema::hasColumn('order_items', 'meter')) {
                $table->dropColumn('meter');
            }
        });
    }
};
