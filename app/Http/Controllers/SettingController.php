<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly BackupService $backups) {}

    public function index(): View
    {
        $dbSize = '—';
        try {
            $dbName = config('database.connections.mysql.database');
            $sizeResult = DB::selectOne("
                SELECT SUM(data_length + index_length) AS db_size
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$dbName]);
            if ($sizeResult && $sizeResult->db_size) {
                $dbSize = round($sizeResult->db_size / (1024 * 1024), 2).' MB';
            }
        } catch (\Throwable) {
            $dbSize = 'سەرکەوتوو نەبوو';
        }

        return view('settings.index', [
            'settings' => Setting::all_(),
            'rates' => ExchangeRate::latest('effective_date')->limit(15)->get(),
            'currentRate' => ExchangeRate::current(),
            'backups' => $this->backups->list(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'systemInfo' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'db_size' => $dbSize,
                'server_time' => now()->format('Y-m-d H:i:s'),
                'timezone' => config('app.timezone'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // زانیاری کارگە
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_phone2' => ['nullable', 'string', 'max:50'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'string', 'max:100'],
            'invoice_footer' => ['nullable', 'string', 'max:255'],

            // وەسڵ و چاپ
            'default_currency' => ['nullable', 'string', 'in:IQD,USD'],
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
            'show_delivery_date' => ['nullable', 'in:0,1'],
            'invoice_terms' => ['nullable', 'string', 'max:500'],

            // دەوام و کارگە
            'workshop_work_start' => ['nullable', 'string', 'max:10'],
            'workshop_work_end' => ['nullable', 'string', 'max:10'],
            'workshop_work_hours' => ['nullable', 'numeric', 'min:1', 'max:24'],
            'workshop_weekly_holiday' => ['nullable', 'string', 'max:20'],
            'workshop_overtime_multiplier' => ['nullable', 'numeric', 'min:0.5', 'max:5'],

            // مەخزەن و ئاگاداری
            'low_stock_alert' => ['nullable', 'in:0,1'],
            'default_warehouse_id' => ['nullable', 'integer'],

            // بەستنەوە بە API
            'xeiqd_api_key' => ['nullable', 'string', 'max:500'],
        ], [], ['company_name' => 'ناوی کارگە']);

        // چارەسەری چکبۆکسەکان ئەگەر دیارینەکرابن
        $data['show_delivery_date'] = $request->has('show_delivery_date') ? '1' : '0';
        $data['low_stock_alert'] = $request->has('low_stock_alert') ? '1' : '0';

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('ok', 'ڕێکخستنەکان بە سەرکەوتوویی پاشەکەوتکران.');
    }

    /** نرخی نوێی دۆلار — مامەڵەی کۆن ناگۆڕێت. */
    public function storeRate(Request $request)
    {
        $data = $request->validate([
            'effective_date' => ['required', 'date'],
            'usd_to_iqd' => ['required', 'numeric', 'gt:0'],
        ], ['usd_to_iqd.gt' => 'نرخ دەبێت لە سفر زیاتر بێت.']);

        ExchangeRate::updateOrCreate(
            ['effective_date' => $data['effective_date']],
            ['usd_to_iqd' => $data['usd_to_iqd'], 'user_id' => auth()->id()],
        );

        return back()->with('ok', 'نرخی دۆلار تۆمارکرا.');
    }

    /** وەرگرتنی نرخی ڕاستەوخۆ لە رێگەی API */
    public function liveRate(\App\Services\ExchangeRateService $service)
    {
        $data = $service->getLiveRateData();
        if ($data) {
            return response()->json([
                'ok' => true,
                'rate_per_usd' => $data['rate_per_usd'],
                'rate_per_100' => $data['rate_per_100'],
                'source' => $data['source'],
            ]);
        }

        return response()->json([
            'ok' => false,
            'message' => 'نەتوانرا پەیوەندی بە API بکرێت.',
        ], 500);
    }

    /** نوێکردنەوەی نرخی ئەمڕۆ لە ڕێگەی API بۆ ناو داتابەیس */
    public function syncLiveRate(\App\Services\ExchangeRateService $service)
    {
        $rate = $service->syncTodayRate();
        if ($rate) {
            return back()->with('ok', "نرخی ئەمڕۆ لە ئینتەرنێت وەرگیرا: {$rate->usd_to_iqd} د.ع");
        }

        return back()->with('err', 'نەتوانرا لە ڕێگەی API نوێ بکرێتەوە. تکایە کلیلی API پشکنین بکە.');
    }

    public function backup()
    {
        try {
            $file = $this->backups->create();
        } catch (\Throwable $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', "باکەپ بە سەرکەوتوویی دروستکرا: {$file}");
    }

    public function download(string $file)
    {
        $path = $this->backups->path($file);

        abort_unless(is_file($path), 404);

        return response()->download($path);
    }

    public function deleteBackup(string $file)
    {
        if ($this->backups->delete($file)) {
            return back()->with('ok', 'فایلی باکەپ سڕایەوە.');
        }

        return back()->with('err', 'فایلەکە نەدۆزرایەوە.');
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            \Illuminate\Support\Facades\Cache::forget(Setting::CACHE_KEY);

            return back()->with('ok', 'کاش و فایلە کاتییەکانی سیستم بە سەرکەوتوویی خاوێنکرانەوە.');
        } catch (\Throwable $e) {
            return back()->with('err', 'هەڵە لە خاوێنکردنەوە: '.$e->getMessage());
        }
    }
}
