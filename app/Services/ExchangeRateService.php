<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * دەرهێنانی نرخی ڕاستەوخۆ لە ڕێگەی API (Live Exchange Rate)
     * سەرچاوەی سەرەکی: DinarLive API (بۆرسەی عێراق و بەغدا/کوردستان).
     */
    public function getLiveRateData(): ?array
    {
        // ١. تاقیکردنەوەی سەرچاوەی سەرەکی (DinarLive)
        try {
            $response = Http::timeout(4)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get('https://dinarlive.com/api/v2/get-price', [
                    'id' => 5,
                    'location' => 'baghdad',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $value = (float) ($data['value'] ?? ($data['price'] ?? 0));
                if ($value > 0) {
                    $ratePer100 = $value > 10000 ? $value : $value * 100;
                    $ratePerUsd = $value > 10000 ? $value / 100 : $value;
                    return [
                        'rate_per_usd' => round($ratePerUsd, 2),
                        'rate_per_100' => round($ratePer100, 2),
                        'source' => 'DinarLive',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('DinarLive API fetch error: ' . $e->getMessage());
        }

        // ٢. ئەگەر DinarLive کاتی دانەخرابوو یان وەڵامی نەدایەوە، پەنا دەبەینە بەر سەرچاوەی یەدەگ
        $fallbacks = [
            'https://open.er-api.com/v6/latest/USD',
            'https://api.exchangerate-api.com/v4/latest/USD',
        ];

        foreach ($fallbacks as $url) {
            try {
                $res = Http::timeout(3)->get($url);
                if ($res->successful()) {
                    $rate = (float) ($res->json('rates.IQD') ?: 0);
                    if ($rate > 0) {
                        return [
                            'rate_per_usd' => round($rate, 2),
                            'rate_per_100' => round($rate * 100, 2),
                            'source' => 'ExchangeRate-API',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Fallback API ({$url}) error: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * دەرهێنانی نرخی هەر $1 بۆ دینار.
     */
    public function fetchLiveRate(): ?float
    {
        $data = $this->getLiveRateData();
        return $data ? $data['rate_per_usd'] : null;
    }

    /**
     * دەرهێنانی نرخی هەر $100 بۆ دینار.
     */
    public function fetchLiveRatePer100(): ?float
    {
        $data = $this->getLiveRateData();
        return $data ? $data['rate_per_100'] : null;
    }

    /**
     * نوێکردنەوە و پاشەکەوتکردنی نرخی ئەمڕۆ لە داتابەیس.
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
