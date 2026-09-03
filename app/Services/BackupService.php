<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * باکەپی داتابەیس — دروستکردن، گەڕاندنەوە، ئەپڵۆد، و باکەپی ئۆتۆماتیکی ڕۆژانە.
 */
class BackupService
{
    private const DISK = 'local';
    private const FOLDER = 'backups';

    /** ڕێگاکانی mysqldump لەسەر mac و لینوکس. */
    private const DUMP_PATHS = [
        '/opt/homebrew/bin/mysqldump',
        '/usr/local/mysql/bin/mysqldump',
        '/opt/homebrew/opt/mysql-client/bin/mysqldump',
        '/usr/bin/mysqldump',
        'mysqldump',
    ];

    /** ڕێگاکانی mysql client بۆ گەڕاندنەوەی باکەپ. */
    private const MYSQL_PATHS = [
        '/opt/homebrew/bin/mysql',
        '/usr/local/mysql/bin/mysql',
        '/opt/homebrew/opt/mysql-client/bin/mysql',
        '/usr/bin/mysql',
        'mysql',
    ];

    /** دروستکردنی باکەپێکی نوێ — ناوی فایل دەگەڕێنێتەوە. */
    public function create(bool $isAuto = false): string
    {
        $binary = $this->findDumpBinary();
        $config = config('database.connections.mysql');
        $prefix = $isAuto ? 'auto-hemin-' : 'hemin-';
        $filename = $prefix . now()->format('Y-m-d_His') . '.sql';
        $path = Storage::disk(self::DISK)->path(self::FOLDER . '/' . $filename);

        Storage::disk(self::DISK)->makeDirectory(self::FOLDER);

        $process = new Process([
            $binary,
            '--host=' . $config['host'],
            '--port=' . $config['port'],
            '--user=' . $config['username'],
            '--single-transaction',
            '--routines',
            '--default-character-set=utf8mb4',
            '--result-file=' . $path,
            $config['database'],
        ], env: ['MYSQL_PWD' => $config['password'] ?? '']);

        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('باکەپ سەرکەوتوو نەبوو: ' . $process->getErrorOutput());
        }

        return $filename;
    }

    /**
     * پشکنینی باکەپی ڕۆژانە:
     * ئەگەر ئەمڕۆ هیچ باکەپێک وەرنەگیرابێت، خۆکارانە باکەپ وەردەگرێت.
     */
    public function ensureDailyBackup(): ?string
    {
        $backups = $this->list();
        $today = now()->toDateString();

        $alreadyBackedUpToday = collect($backups)->contains(function ($b) use ($today) {
            return date('Y-m-d', $b['created_at']) === $today;
        });

        if ($alreadyBackedUpToday) {
            return null;
        }

        $filename = $this->create(isAuto: true);
        $this->cleanOld(30);

        return $filename;
    }

    /** گەڕاندنەوەی باکەپ بۆ ناو داتابەیس. */
    public function restore(string $filename): void
    {
        $path = $this->path($filename);

        if (! is_file($path)) {
            throw new \RuntimeException('فایلی باکەپ نەدۆزرایەوە.');
        }

        $binary = $this->findMysqlBinary();
        $config = config('database.connections.mysql');

        $process = new Process([
            $binary,
            '--host=' . $config['host'],
            '--port=' . $config['port'],
            '--user=' . $config['username'],
            '--default-character-set=utf8mb4',
            $config['database'],
        ], env: ['MYSQL_PWD' => $config['password'] ?? '']);

        $process->setInput(file_get_contents($path));
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('گەڕاندنەوەی داتابەیس سەرکەوتوو نەبوو: ' . $process->getErrorOutput());
        }
    }

    /** ئەپڵۆدکردنی فایلی دەرەکی بۆ بوخچەی باکەپەکان. */
    public function upload(UploadedFile $file): string
    {
        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = 'imported-' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $origName) . '-' . now()->format('Y-m-d_His') . '.sql';

        Storage::disk(self::DISK)->makeDirectory(self::FOLDER);
        $file->storeAs(self::FOLDER, $safeName, self::DISK);

        return $safeName;
    }

    /** سڕینەوەی باکەپە زۆر کۆنەکان (زیاتر لە 30 ڕۆژ). */
    public function cleanOld(int $keepDays = 30): int
    {
        $disk = Storage::disk(self::DISK);
        $threshold = now()->subDays($keepDays)->timestamp;
        $deleted = 0;

        foreach ($this->list() as $backup) {
            if ($backup['created_at'] < $threshold) {
                $this->delete($backup['name']);
                $deleted++;
            }
        }

        return $deleted;
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
                'is_auto' => str_starts_with(basename($file), 'auto-'),
            ])
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function path(string $filename): string
    {
        $safe = basename($filename);

        return Storage::disk(self::DISK)->path(self::FOLDER . '/' . $safe);
    }

    public function delete(string $filename): bool
    {
        $safe = basename($filename);
        $file = self::FOLDER . '/' . $safe;

        if (Storage::disk(self::DISK)->exists($file)) {
            return Storage::disk(self::DISK)->delete($file);
        }

        return false;
    }

    private function findDumpBinary(): string
    {
        foreach (self::DUMP_PATHS as $path) {
            if ($path === 'mysqldump' || is_executable($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('mysqldump نەدۆزرایەوە.');
    }

    private function findMysqlBinary(): string
    {
        foreach (self::MYSQL_PATHS as $path) {
            if ($path === 'mysql' || is_executable($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('mysql نەدۆزرایەوە.');
    }
}
