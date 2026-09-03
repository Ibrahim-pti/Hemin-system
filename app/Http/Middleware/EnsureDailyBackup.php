<?php

namespace App\Http\Middleware;

use App\Services\BackupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureDailyBackup
{
    public function handle(Request $request, Closure $next): Response
    {
        // تەنها بۆ بەکارهێنەری داخڵبوو لە وێبگەڕدا
        if ($request->user() && $request->isMethod('GET') && ! $request->expectsJson()) {
            $today = now()->toDateString();
            $cacheKey = 'daily_auto_backup_' . $today;

            if (! Cache::has($cacheKey)) {
                try {
                    app(BackupService::class)->ensureDailyBackup();
                    Cache::put($cacheKey, true, now()->endOfDay());
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('خۆکار باکەپ شکستی هێنا: ' . $e->getMessage());
                }
            }
        }

        return $next($request);
    }
}
