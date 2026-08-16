<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_for_sale')->default(false)->after('sale_price');
        });

        // هەر بابەتێک نرخی فرۆشتنی هەبێت دەیکەینە بابەتی فرۆشتن.
        \Illuminate\Support\Facades\DB::table('items')
            ->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->update(['is_for_sale' => true]);
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('is_for_sale');
        });
    }
};
