<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * دەرهێنانی نرخی ڕاستەوخۆی دۆلار بۆ هەولێر لە ڕێگەی SmartTraderIraq API
     * سەرچاوە: https://smarttraderiraq.com/currency
     */
    public function getLiveRateData(): ?array
    {
        // ١. وەرگرتنی ڕاستەوخۆ لە SmartTraderIraq (نرخی هەولێر و کوردستان)
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get('https://smarttraderiraq.com:2096/grouped_currency_forclient?lang=krd');

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['status']) && !empty($data['data'])) {
                    $currencies = collect($data['data'])->firstWhere('_id', 'Currencies');
                    if ($currencies && !empty($currencies['opposite_currency_price'][0])) {
                        $ratePer100 = (float) str_replace(',', '', (string) $currencies['opposite_currency_price'][0]);
                        if ($ratePer100 > 1000) {
                            $ratePerUsd = $ratePer100 > 10000 ? $ratePer100 / 100 : $ratePer100;
                            $rate100 = $ratePer100 > 10000 ? $ratePer100 : $ratePer100 * 100;

                            return [
                                'rate_per_usd' => round($ratePerUsd, 2),
                                'rate_per_100' => round($rate100, 2),
                                'location' => 'Erbil (هەولێر)',
                                'source' => 'SmartTraderIraq',
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SmartTraderIraq API fetch error: ' . $e->getMessage());
        }

        // ٢. ئەگەر سێرڤەر کاتی وەڵامی نەدایەوە، پەنا دەبەینە بەر سەرچاوەی یەدەگ
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
                            'location' => 'Erbil (هەولێر)',
                            'source' => 'Fallback API',
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
