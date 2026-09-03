<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run {--auto : دەستنیشانکردنی باکەپ وەک ئۆتۆماتیک}';
    protected $description = 'دروستکردنی باکەپی داتابەیسی سیستەم بە فۆرماتی .sql';

    public function handle(BackupService $backupService): int
    {
        $this->info('دەستکرا بە دروستکردنی باکەپی داتابەیس...');

        try {
            $isAuto = (bool) $this->option('auto');
            $file = $backupService->create($isAuto);
            $backupService->cleanOld(30);

            $this->info("باکەپ بە سەرکەوتوویی دروستکرا: {$file}");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("هەڵە لە دروستکردنی باکەپ: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
