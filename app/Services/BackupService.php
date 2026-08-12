<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * باکەپی داتابەیس — فایلێکی .sql لە storage/app/backups دروست دەکات.
 * مێژووی هەموو شتێک لەناویدایە، بۆیە دەکرێت سیستەم لێی بگەڕێنرێتەوە.
 */
class BackupService
{
    private const DISK = 'local';
    private const FOLDER = 'backups';

    /** ڕێگاکانی ئەگەری mysqldump لەسەر mac و لینوکس. */
    private const DUMP_PATHS = [
        '/usr/local/mysql/bin/mysqldump',
        '/opt/homebrew/opt/mysql-client/bin/mysqldump',
        '/opt/homebrew/bin/mysqldump',
        '/usr/bin/mysqldump',
        'mysqldump',
    ];

    /** دروستکردنی باکەپێکی نوێ — ناوی فایل دەگەڕێنێتەوە. */
    public function create(): string
    {
        $binary = $this->findBinary();
        $config = config('database.connections.mysql');
        $filename = 'hemin-'.now()->format('Y-m-d_His').'.sql';
        $path = Storage::disk(self::DISK)->path(self::FOLDER.'/'.$filename);

        Storage::disk(self::DISK)->makeDirectory(self::FOLDER);

        $process = new Process([
            $binary,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--single-transaction',
            '--routines',
            '--default-character-set=utf8mb4',
            '--result-file='.$path,
            $config['database'],
        ], env: ['MYSQL_PWD' => $config['password'] ?? '']);

        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('باکەپ سەرکەوتوو نەبوو: '.$process->getErrorOutput());
        }

        return $filename;
    }

    /** لیستی باکەپەکان — نوێترین لە سەرەوە. */
    public function list(): array
    {
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists(self::FOLDER)) {
            return [];
        }

        return collect($disk->files(self::FOLDER))
            ->filter(fn (string $file) => str_ends_with($file, '.sql'))
            ->map(fn (string $file) => [
                'name' => basename($file),
                'size' => $disk->size($file),
                'created_at' => $disk->lastModified($file),
            ])
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function path(string $filename): string
    {
        // ڕێگری لە دەرچوون لە بوخچەی باکەپ.
        $safe = basename($filename);

        return Storage::disk(self::DISK)->path(self::FOLDER.'/'.$safe);
    }

    private function findBinary(): string
    {
        foreach (self::DUMP_PATHS as $path) {
            if ($path === 'mysqldump' || is_executable($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('mysqldump نەدۆزرایەوە.');
    }
}
