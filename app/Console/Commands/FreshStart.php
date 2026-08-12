<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * پاککردنەوەی هەموو مامەڵەکان بۆ دەستپێکردنی کاری ڕاستەقینە.
 * بەکارهێنەر، رۆڵ، ڕێکخستن، یەکە و کۆگاکان دەمێننەوە.
 */
class FreshStart extends Command
{
    protected $signature = 'hemin:fresh {--force : بەبێ پرسیار}';

    protected $description = 'سڕینەوەی هەموو مامەڵەکان (وەسڵ، کڕین، حەقدی، مەخزەن) — داتای بنەڕەت دەمێنێتەوە';

    /** ئەم تەیبڵانە بە تەواوی پاک دەکرێنەوە. */
    private const TABLES = [
        'stock_movements',
        'stock_count_items',
        'stock_counts',
        'order_items',
        'orders',
        'purchase_items',
        'purchases',
        'payments',
        'cash_transactions',
        'cash_closings',
        'external_jobs',
        'attendances',
        'activity_log',
    ];

    public function handle(): int
    {
        $this->warn('ئەمە هەموو وەسڵ، پسوولە، حەقدی و جوڵەی مەخزەن دەسڕێتەوە.');
        $this->line('دەمێننەوە: بەکارهێنەر، رۆڵ، ڕێکخستن، یەکە، کۆگا، جۆری کاڵا.');

        if (! $this->option('force') && ! $this->confirm('دڵنیایت؟')) {
            $this->info('هەڵوەشێنرایەوە.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();

        foreach (self::TABLES as $table) {
            DB::table($table)->truncate();
            $this->line("  پاککرایەوە: {$table}");
        }

        // کاڵا، کڕیار و فرۆشیاریش دەسڕدرێنەوە — چونکە بێ مامەڵە بەکارنایەن.
        foreach (['items', 'customers', 'suppliers', 'employees'] as $table) {
            DB::table($table)->truncate();
            $this->line("  پاککرایەوە: {$table}");
        }

        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info('✅ سیستەم ئامادەیە بۆ کاری ڕاستەقینە.');

        return self::SUCCESS;
    }
}
