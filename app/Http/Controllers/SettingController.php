<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Setting;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly BackupService $backups) {}

    public function index(): View
    {
        return view('settings.index', [
            'settings' => Setting::all_(),
            'rates' => ExchangeRate::latest('effective_date')->limit(15)->get(),
            'currentRate' => ExchangeRate::current(),
            'backups' => $this->backups->list(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_phone2' => ['nullable', 'string', 'max:50'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'invoice_footer' => ['nullable', 'string', 'max:255'],
        ], [], ['company_name' => 'ناوی کارگە']);

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('ok', 'ڕێکخستنەکان پاشەکەوتکران.');
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
        $rate = $service->fetchLiveRate();
        if ($rate) {
            return response()->json([
                'ok' => true,
                'rate_per_usd' => $rate,
                'rate_per_100' => $rate * 100,
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

        return back()->with('err', 'نەتوانرا لە ڕێگەی API نوێ بکرێتەوە.');
    }

    public function backup()
    {
        try {
            $file = $this->backups->create();
        } catch (\Throwable $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', "باکەپ دروستکرا: {$file}");
    }

    public function download(string $file)
    {
        $path = $this->backups->path($file);

        abort_unless(is_file($path), 404);

        return response()->download($path);
    }
}
