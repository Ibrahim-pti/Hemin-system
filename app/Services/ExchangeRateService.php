<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * دەرهێنانی نوێترین نرخی دۆلاری بازاڕی هەولێر و کوردستان (Cash Market Rate)
     */
    public function getLiveRateData(bool $forceRefresh = false): ?array
    {
        if ($forceRefresh) {
            Cache::forget('hemin_live_exchange_rate');
        }

        return Cache::remember('hemin_live_exchange_rate', 1800, function () {
            return $this->fetchErbilMarketRate();
        });
    }

    private function fetchErbilMarketRate(): ?array
    {
        // ١. دەرهێنانی ڕاستەوخۆی نرخی بازاڕی هەولێر لە SmartTraderIraq
        try {
            $response = Http::timeout(4)
                ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'Mozilla/5.0'])
                ->get('https://smarttraderiraq.com:2096/grouped_currency_forclient?lang=krd');

            if ($response->successful()) {
                $data = $response->json();
                $currencies = collect($data['data'] ?? [])->firstWhere('_id', 'Currencies');
                if (!empty($currencies['opposite_currency_price'][0])) {
                    $rate100 = (float) str_replace(',', '', (string) $currencies['opposite_currency_price'][0]);
                    if ($rate100 > 100000) { // نرخی بازاڕی حەقیقی سەروو ١٠٠ هەزارە
                        return [
                            'rate_per_usd' => round($rate100 / 100, 2),
                            'rate_per_100' => round($rate100, 2),
                            'city' => 'Erbil (هەولێر)',
                            'source' => 'بازاڕی هەولێر',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Erbil market rate fetch error: ' . $e->getMessage());
        }

        // ٢. ئەگەر سێرڤەر بەردەست نەبوو، نرخی تۆمارکراوی پێشووی داتابەیس بەکاردێت (وەک ١٥٠،٠٠٠)
        $dbRate = ExchangeRate::current();
        if ($dbRate > 100) {
            $rate100 = $dbRate > 10000 ? $dbRate : $dbRate * 100;
            $rateUsd = $dbRate > 10000 ? $dbRate / 100 : $dbRate;
            return [
                'rate_per_usd' => round($rateUsd, 2),
                'rate_per_100' => round($rate100, 2),
                'city' => 'Erbil (هەولێر)',
                'source' => 'داتابەیسی سیستەم',
            ];
        }

        // نرخی بنەڕەتی گریمانەیی بازاڕی هەولێر
        return [
            'rate_per_usd' => 1500.0,
            'rate_per_100' => 150000.0,
            'city' => 'Erbil (هەولێر)',
            'source' => 'نرخی بازاڕی هەولێر',
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
