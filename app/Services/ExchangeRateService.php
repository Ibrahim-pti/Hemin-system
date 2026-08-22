<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * دەرهێنانی نوێترین نرخی دۆلار لە ماڵپەڕی Barchn (https://barchn.com/exchangerate)
     */
    public function getLiveRateData(bool $forceRefresh = false): ?array
    {
        if ($forceRefresh) {
            Cache::forget('hemin_barchn_exchange_rate');
        }

        return Cache::remember('hemin_barchn_exchange_rate', 900, function () {
            return $this->fetchFromBarchn();
        });
    }

    private function fetchFromBarchn(): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'ku,ar,en-US,en;q=0.9',
                ])
                ->get('https://barchn.com/exchangerate');

            if ($response->successful()) {
                $html = $response->body();

                // ڕێگەی یەکەم: دەرهێنان لە snapshot json
                if (preg_match('/(?:&quot;|")usDollar(?:&quot;|"):\s*([0-9.]+)/', $html, $m)) {
                    $rate100 = (float) $m[1];
                    if ($rate100 > 50000) {
                        return [
                            'rate_per_usd' => round($rate100 / 100, 2),
                            'rate_per_100' => round($rate100, 2),
                            'source' => 'Barchn (نرخی بازاڕ)',
                        ];
                    }
                }

                // ڕێگەی دووەم: دەرهێنان لە خشتەی HTML (100 USD -> 154,150)
                if (preg_match('/100\s*USD.*?([0-9,]+(?:\.[0-9]+)?)\s*د\.ع/s', $html, $m)) {
                    $rate100 = (float) str_replace(',', '', $m[1]);
                    if ($rate100 > 50000) {
                        return [
                            'rate_per_usd' => round($rate100 / 100, 2),
                            'rate_per_100' => round($rate100, 2),
                            'source' => 'Barchn (نرخی بازاڕ)',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Barchn exchange rate fetch error: ' . $e->getMessage());
        }

        // ئەگەر کێشەی ئینتەرنێت هەبوو، نرخی تۆمارکراوی پێشووی داتابەیس بەکاربێنە
        $dbRate = ExchangeRate::current();
        if ($dbRate > 100) {
            $rate100 = $dbRate > 10000 ? $dbRate : $dbRate * 100;
            $rateUsd = $dbRate > 10000 ? $dbRate / 100 : $dbRate;
            return [
                'rate_per_usd' => round($rateUsd, 2),
                'rate_per_100' => round($rate100, 2),
                'source' => 'داتابەیسی سیستەم',
            ];
        }

        return [
            'rate_per_usd' => 1500.0,
            'rate_per_100' => 150000.0,
            'source' => 'نرخی بنەڕەتی',
        ];
    }

    /**
     * دەرهێنانی نرخی هەر $1 بۆ دینار.
     */
    public function fetchLiveRate(): ?float
    {
        $data = $this->getLiveRateData();
        return $data ? $data['rate_per_usd'] : 1500.0;
    }

    /**
     * دەرهێنانی نرخی هەر $100 بۆ دینار.
     */
    public function fetchLiveRatePer100(): ?float
    {
        $data = $this->getLiveRateData();
        return $data ? $data['rate_per_100'] : 150000.0;
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
