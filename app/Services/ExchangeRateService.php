<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * دەرهێنانی نوێترین نرخی دۆلار لە ماڵپەڕی قمر الفجر (https://qamaralfajr.com/production/exchange_rates.php)
     */
    public function getLiveRateData(bool $forceRefresh = false): ?array
    {
        if ($forceRefresh) {
            Cache::forget('hemin_live_exchange_rate');
        }

        return Cache::remember('hemin_live_exchange_rate', 300, function () {
            // سەرچاوەی یەکەم: قمر الفجر
            $qamarData = $this->fetchFromQamarAlFajr();
            if ($qamarData) {
                return $qamarData;
            }

            // سەرچاوەی یەدەگ: Barchn
            $barchnData = $this->fetchFromBarchn();
            if ($barchnData) {
                return $barchnData;
            }

            // ئەگەر کێشەی ئینتەرنێت هەبوو، کۆتا نرخی ئەپدەیتکراوی داتابەیس بهێنە
            $lastDbRate = ExchangeRate::query()
                ->orderByDesc('effective_date')
                ->orderByDesc('id')
                ->first();

            if ($lastDbRate && (float) $lastDbRate->usd_to_iqd > 0) {
                $val = (float) $lastDbRate->usd_to_iqd;
                $rateUsd = $val > 10000 ? $val / 100 : $val;
                $rate100 = $val > 10000 ? $val : $val * 100;

                return [
                    'rate_per_usd' => round($rateUsd, 2),
                    'rate_per_100' => round($rate100, 2),
                    'source' => 'کۆتا نرخی ئەپدەیتکراو (' . fmt_date($lastDbRate->effective_date) . ')',
                ];
            }

            return null;
        });
    }

    /**
     * وەرگرتنی نرخ لە ماڵپەڕی فەرمی قمر الفجر
     */
    private function fetchFromQamarAlFajr(): ?array
    {
        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get('https://qamaralfajr.com/production/exchange_rates.php');

            if ($response->successful()) {
                $html = $response->body();

                // دۆزینەوەی دێڕی دینار IQD لە خشتەی قمر الفجر:
                // <tr ...><td ...><button ...> 154500</button></td><td ...><button ...>153750</button></td>...IQD...
                if (preg_match('/<tr[^>]*>.*?<button[^>]*>\s*([0-9,.]+)\s*<\/button>.*?<button[^>]*>\s*([0-9,.]+)\s*<\/button>.*?IQD.*?<\/tr>/is', $html, $m)) {
                    $sellRate = (float) str_replace(',', '', $m[1]);
                    $buyRate = (float) str_replace(',', '', $m[2]);

                    // لە کارگەدا نرخی فرۆشتن (Sell Rate) یان نرخی ١٠٠ دۆلار پێوەری سەرەکییە
                    $rate100 = $sellRate > 50000 ? $sellRate : ($buyRate > 50000 ? $buyRate : 0);

                    if ($rate100 > 50000) {
                        return [
                            'rate_per_usd' => round($rate100 / 100, 2),
                            'rate_per_100' => round($rate100, 2),
                            'sell_rate' => $sellRate,
                            'buy_rate' => $buyRate,
                            'source' => 'قمر الفجر (Qamar Al Fajr)',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Qamar Al Fajr exchange rate fetch error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * وەرگرتنی نرخ لە Barchn وەک سەرچاوەی یەدەگ
     */
    private function fetchFromBarchn(): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get('https://barchn.com/exchangerate');

            if ($response->successful()) {
                $html = $response->body();

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

        return null;
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
