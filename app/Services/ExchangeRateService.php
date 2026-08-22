<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * دەرهێنانی نرخی ڕاستەوخۆ لە ڕێگەی API (Live Exchange Rate)
     * نرخی هەر $1 بۆ دینار (IQD).
     */
    public function fetchLiveRate(): ?float
    {
        $apis = [
            'https://open.er-api.com/v6/latest/USD',
            'https://api.exchangerate-api.com/v4/latest/USD',
        ];

        foreach ($apis as $url) {
            try {
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $rate = $response->json('rates.IQD');
                    if ($rate && $rate > 0) {
                        return round((float) $rate, 2);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to fetch exchange rate from {$url}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * نوێکردنەوە و پاشەکەوتکردنی نرخی ئەمڕۆ لە داتابەیس لە ڕێگەی API.
     */
    public function syncTodayRate(?float $customRate = null): ?ExchangeRate
    {
        $rate = $customRate ?: $this->fetchLiveRate();
        if ($rate) {
            return ExchangeRate::updateOrCreate(
                ['effective_date' => now()->toDateString()],
                ['usd_to_iqd' => $rate, 'user_id' => auth()->id()]
            );
        }

        return null;
    }
}
