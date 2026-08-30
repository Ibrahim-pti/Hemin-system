<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Setting;
use App\Models\User;
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
        $users = User::all();
        $adminUser = $users->firstWhere('id', 1) ?? $users->first();
        $workshopUser = $users->firstWhere('id', 2) ?? $users->skip(1)->first();

        return view('settings.index', [
            'settings' => Setting::all_(),
            'backups' => $this->backups->list(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'adminUser' => $adminUser,
            'workshopUser' => $workshopUser,
            'users' => $users,
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'ئەم ئیمەیڵە پێشتر لەلایەن هەژمارێکی ترەوە بەکارهاتووە.',
            'password.min' => 'پاسۆرد دەبێت لانی کەم ٦ پیت یان ژمارە بێت.',
            'password.confirmed' => 'دووپاتکردنەوەی وشەی نهێنی وەک یەک نییە.',
        ], [
            'name' => 'ناو',
            'email' => 'ئیمەیڵ',
            'password' => 'وشەی نهێنی',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return back()->with('ok', "زانیاری و وشەی نهێنی هەژماری [{$user->name}] بە سەرکەوتوویی نوێکرایەوە.");
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
