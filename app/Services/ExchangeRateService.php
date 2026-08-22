<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * دەرهێنانی نرخی ڕاستەوخۆ بۆ هەولێر لە ڕێگەی XEIQD API
     * بەڵگەنامە: GET api/v1/latest?city=Erbil&amount=100
     */
    public function getLiveRateData(): ?array
    {
        $token = Setting::get('xeiqd_api_key') ?: config('services.xeiqd.key');
        $baseUrl = Setting::get('xeiqd_api_url') ?: config('services.xeiqd.url', 'https://xeiqd.com/api/v1/latest');

        // ١. پەیوەندی بە XEIQD API بۆ نرخی شاری هەولێر
        try {
            $req = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]);

            if (!empty($token)) {
                $req = $req->withToken($token);
            }

            $response = $req->get($baseUrl, [
                'city' => 'Erbil',
                'amount' => 100,
                'lang' => 'KU',
                'base' => 'USD',
                'symbols' => 'IQD',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rate100 = (float) ($data['rates']['IQD'] ?? ($data['rates']['iqd'] ?? 0));

                if ($rate100 > 1000) {
                    $ratePerUsd = $rate100 > 10000 ? $rate100 / 100 : $rate100;
                    $ratePer100 = $rate100 > 10000 ? $rate100 : $rate100 * 100;

                    return [
                        'rate_per_usd' => round($ratePerUsd, 2),
                        'rate_per_100' => round($ratePer100, 2),
                        'city' => $data['city'] ?? 'Erbil (هەولێر)',
                        'source' => 'XEIQD API',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('XEIQD API fetch error: ' . $e->getMessage());
        }

        // ٢. سەرچاوەی یەدەگی هەولێر ئەگەر XEIQD لەکار بوو یان توکن بەسەرچووبوو
        try {
            $response = Http::timeout(4)
                ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'Mozilla/5.0'])
                ->get('https://smarttraderiraq.com:2096/grouped_currency_forclient?lang=krd');

            if ($response->successful()) {
                $data = $response->json();
                $currencies = collect($data['data'] ?? [])->firstWhere('_id', 'Currencies');
                if (!empty($currencies['opposite_currency_price'][0])) {
                    $rate100 = (float) str_replace(',', '', (string) $currencies['opposite_currency_price'][0]);
                    if ($rate100 > 1000) {
                        return [
                            'rate_per_usd' => round($rate100 / 100, 2),
                            'rate_per_100' => round($rate100, 2),
                            'city' => 'Erbil (هەولێر)',
                            'source' => 'SmartTrader (Erbil)',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SmartTrader fallback error: ' . $e->getMessage());
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
