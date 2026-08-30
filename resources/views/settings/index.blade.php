@extends('layouts.app')
@section('title', 'ڕێکخستن و باکەپ')

@section('content')

{{-- سەردێڕی پەڕە --}}
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-xl font-black text-slate-900">⚙️ ڕێکخستنەکانی سیستم و باکەپ</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">بەڕێوەبردنی زانیاری کارگە، وەسڵ، کاتەکانی دەوام، نرخی دۆلار و پاراستنی داتابەیس</p>
    </div>

    {{-- دوگمەی خاوێنکردنەوەی خێرای کاش --}}
    <form method="POST" action="{{ route('settings.clear-cache') }}">
        @csrf
        <button type="submit" class="btn btn-ghost !py-1.5 !px-3 text-xs bg-white border border-slate-200 shadow-2xs hover:bg-slate-50 text-slate-700 font-bold inline-flex items-center gap-1.5 cursor-pointer">
            🧹 خاوێنکردنەوەی کاش
        </button>
    </form>
</div>

<div class="grid gap-6 lg:grid-cols-3">

    {{-- ستوونی ڕاست / سەرەکی: فۆڕمەکانی ڕێکخستن --}}
    <div class="space-y-6 lg:col-span-2">

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf @method('PUT')

            {{-- ١. زانیاری کارگە --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3 mb-4">
                    <span class="size-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">🏢</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">زانیاری کارگە و چاپەمەنی</h2>
                        <p class="text-[11px] text-slate-500">ئەم زانیارییانە لە سەردێڕی وەسڵ و حەقدییەکان چاپ دەکرێن.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="label" for="company_name">ناوی کارگە / کۆمپانیا <span class="text-rose-500">*</span></label>
                        <input id="company_name" name="company_name" class="field font-bold" required
                               placeholder="بۆ نموونە: کارگەی ئاسنگەری هێمن"
                               value="{{ old('company_name', $settings['company_name'] ?? '') }}">
                    </div>

                    <div>
                        <label class="label" for="company_tagline">دەقی ژێر ناو (دروشم)</label>
                        <input id="company_tagline" name="company_tagline" class="field"
                               placeholder="بۆ نموونە: بۆ دروستکردنی دەرگا و مەحەجەرە و کەپر و مەسعەد بەشێوازێکی ئەندەسی"
                               value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="company_phone">تەلەفۆنی سەرەکی</label>
                            <input id="company_phone" name="company_phone" class="field num" dir="ltr"
                                   placeholder="07504568556"
                                   value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                        </div>
                        <div>
                            <label class="label" for="company_phone2">تەلەفۆنی دووەم</label>
                            <input id="company_phone2" name="company_phone2" class="field num" dir="ltr"
                                   placeholder="07501201110"
                                   value="{{ old('company_phone2', $settings['company_phone2'] ?? '') }}">
                        </div>
                    </div>

                    <div>
                        <label class="label" for="company_address">ناونیشانی کارگە</label>
                        <input id="company_address" name="company_address" class="field"
                               placeholder="بۆ نموونە: هەولێر — ١٠٠م بەرامبەر گۆڕستانی شێخ ئەحمەد"
                               value="{{ old('company_address', $settings['company_address'] ?? '') }}">
                    </div>

                    <div>
                        <label class="label" for="invoice_footer">دەقی ژێرەوەی وەسڵ (مەرجەکان)</label>
                        <input id="invoice_footer" name="invoice_footer" class="field"
                               placeholder="هەڵە دەگەڕێتەوە بۆ هەردوو لا"
                               value="{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}">
                    </div>
                </div>
            </div>

            {{-- ٢. ڕێکخستنی وەسڵ و دراو --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3 mb-4">
                    <span class="size-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">🧾</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">ڕێکخستنی وەسڵ و فرۆشتن</h2>
                        <p class="text-[11px] text-slate-500">دراوی بنەڕەت و شێوازی تۆمارکردنی داواکارییەکان.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="default_currency">دراوی بنەڕەتی سیستم</label>
                        <select id="default_currency" name="default_currency" class="field font-bold">
                            <option value="IQD" @selected(($settings['default_currency'] ?? 'IQD') === 'IQD')>دیناری عێراقی (IQD)</option>
                            <option value="USD" @selected(($settings['default_currency'] ?? '') === 'USD')>دۆلاری ئەمریکی (USD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="label" for="invoice_prefix">پێشگری ژمارەی وەسڵ (Prefix)</label>
                        <input id="invoice_prefix" name="invoice_prefix" class="field font-mono"
                               placeholder="بۆ نموونە: INV-"
                               value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? '') }}">
                    </div>
                </div>
            </div>

            {{-- ٣. دەوام و کاتەکانی کارگە --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3 mb-4">
                    <span class="size-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">🏭</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">ڕێکخستنی دەوام و کارمەندان</h2>
                        <p class="text-[11px] text-slate-500">پێوەرەکانی دەوام، کاتژمێری ئیشکردن و زیادەکار (Overtime).</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label" for="workshop_work_start">دەستپێکی دەوام</label>
                        <input id="workshop_work_start" name="workshop_work_start" type="time" class="field num font-bold"
                               value="{{ old('workshop_work_start', $settings['workshop_work_start'] ?? '08:00') }}">
                    </div>

                    <div>
                        <label class="label" for="workshop_work_end">کۆتایی دەوام</label>
                        <input id="workshop_work_end" name="workshop_work_end" type="time" class="field num font-bold"
                               value="{{ old('workshop_work_end', $settings['workshop_work_end'] ?? '17:00') }}">
                    </div>

                    <div>
                        <label class="label" for="workshop_work_hours">کاتژمێری فەرمی</label>
                        <input id="workshop_work_hours" name="workshop_work_hours" type="number" step="0.5" min="1" max="24" class="field num font-bold"
                               value="{{ old('workshop_work_hours', $settings['workshop_work_hours'] ?? '8') }}">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 mt-4">
                    <div>
                        <label class="label" for="workshop_weekly_holiday">پشووی هەفتانە</label>
                        <select id="workshop_weekly_holiday" name="workshop_weekly_holiday" class="field font-bold">
                            <option value="friday" @selected(($settings['workshop_weekly_holiday'] ?? 'friday') === 'friday')>هەینی</option>
                            <option value="saturday" @selected(($settings['workshop_weekly_holiday'] ?? '') === 'saturday')>شەممە</option>
                            <option value="thursday" @selected(($settings['workshop_weekly_holiday'] ?? '') === 'thursday')>پێنجشەممە</option>
                            <option value="sunday" @selected(($settings['workshop_weekly_holiday'] ?? '') === 'sunday')>یەکشەممە</option>
                            <option value="none" @selected(($settings['workshop_weekly_holiday'] ?? '') === 'none')>بێ پشوو</option>
                        </select>
                    </div>

                    <div>
                        <label class="label" for="workshop_overtime_multiplier">نرخی کاتژمێری زیادەکار (Overtime)</label>
                        <input id="workshop_overtime_multiplier" name="workshop_overtime_multiplier" type="number" step="0.05" min="0.5" max="5" class="field num font-bold"
                               placeholder="1.0"
                               value="{{ old('workshop_overtime_multiplier', $settings['workshop_overtime_multiplier'] ?? '1.0') }}">
                    </div>
                </div>
            </div>

            {{-- ٤. مەخزەن و ئاگاداری کەمی کاڵا --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3 mb-4">
                    <span class="size-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">📦</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">ڕێکخستنی مەخزەن و کۆگا</h2>
                        <p class="text-[11px] text-slate-500">ئاگادارکردنەوەی کەمی کاڵا و کۆگای بنەڕەت.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="low_stock_alert" value="1"
                               class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                               @checked(($settings['low_stock_alert'] ?? '1') === '1')>
                        <span class="text-xs font-bold text-slate-800">چالاککردنی ئاگاداری کەمی کاڵا لە داشبۆرد و مەخزەن کاتێک دەگاتە سنووری کەمترین بڕ (Min Qty)</span>
                    </label>

                    @if ($warehouses->isNotEmpty())
                        <div>
                            <label class="label" for="default_warehouse_id">کۆگای سەرەکی سیستم</label>
                            <select id="default_warehouse_id" name="default_warehouse_id" class="field font-bold">
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->id }}" @selected(($settings['default_warehouse_id'] ?? '') == $wh->id || ($wh->is_default && empty($settings['default_warehouse_id'])))>
                                        {{ $wh->name }} {{ $wh->is_default ? '(بنەڕەت)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ٥. بەستنەوەی نرخی دۆلار لە API --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3 mb-4">
                    <span class="size-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm">🌐</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">بەستنەوە بە نرخی زیندوی بازاڕ (Live API)</h2>
                        <p class="text-[11px] text-slate-500">کلیلی API بۆ وەرگرتنی خۆکاری نرخی ١٠٠ دۆلاری بازاڕەکانی عێراق و هەرێمی کوردستان.</p>
                    </div>
                </div>

                <div>
                    <label class="label" for="xeiqd_api_key">کلیلی تایبەتی API (XE-IQD API Key)</label>
                    <input id="xeiqd_api_key" name="xeiqd_api_key" type="password" class="field font-mono text-xs"
                           placeholder="کلیلی API بنووسە لێرە..."
                           value="{{ old('xeiqd_api_key', $settings['xeiqd_api_key'] ?? '') }}">
                </div>
            </div>

            {{-- دوگمەی پاشەکەوتکردنی گشتی --}}
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn btn-primary !py-2.5 !px-8 text-sm font-black shadow-md cursor-pointer">
                    💾 پاشەکەوتکردنی هەموو ڕێکخستنەکان
                </button>
            </div>
        </form>

    </div>


    {{-- ستوونی چەپ: نرخی دۆلار، باکەپەکان و زانیاری سێرڤەر --}}
    <div class="space-y-6">

        {{-- ١. کارتی نرخی دۆلار --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="size-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">💵</span>
                    <h3 class="font-bold text-slate-900 text-xs">نرخی دۆلار (USD / IQD)</h3>
                </div>

                <form method="POST" action="{{ route('settings.sync-rate') }}">
                    @csrf
                    <button class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all inline-flex items-center gap-1 cursor-pointer" title="وەرگرتنی نرخی ئەمڕۆ لە ئینتەرنێت">
                        🔄 نوێکردنەوە لە API
                    </button>
                </form>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 text-center mb-4">
                <span class="text-xs text-slate-500 font-bold">نرخی کارای ئێستا:</span>
                <div class="num text-2xl font-black text-slate-900 mt-1">
                    {{ fmt_num($currentRate) }} <span class="text-xs font-bold text-slate-600">د.ع بۆ هەر $١</span>
                </div>
                <div class="text-[11px] font-bold text-emerald-700 mt-1">
                    (١٠٠ دۆلار = {{ fmt_num($currentRate * 100) }} دینار)
                </div>
            </div>

            {{-- فۆڕمی تۆمارکردنی نرخ بە دەست --}}
            <form method="POST" action="{{ route('settings.rate') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label text-[11px]">بەرواری کارابوون</label>
                    <input type="date" name="effective_date" class="field num text-xs" required
                           value="{{ now()->toDateString() }}">
                </div>

                <div>
                    <label class="label text-[11px]">نرخی ١ دۆلار بە دینار</label>
                    <input type="number" step="0.01" min="1" name="usd_to_iqd" class="field num font-bold text-xs" required
                           value="{{ $currentRate }}">
                </div>

                <button type="submit" class="btn btn-primary w-full !py-2 text-xs font-bold cursor-pointer">
                    + تۆمارکردنی نرخی ڕۆژ
                </button>
            </form>

            {{-- مێژووی نرخەکان --}}
            <div class="mt-4 border-t border-slate-100 pt-3">
                <span class="text-[11px] font-bold text-slate-500 block mb-2">مێژووی دوایین نرخەکان:</span>
                <div class="max-h-40 overflow-y-auto divide-y divide-slate-100 text-xs">
                    @forelse ($rates as $rate)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="num text-slate-600 text-[11px]">{{ fmt_date($rate->effective_date) }}</span>
                            <span class="num font-bold text-slate-900">{{ fmt_num($rate->usd_to_iqd) }} د.ع</span>
                        </div>
                    @empty
                        <div class="text-center py-2 text-slate-400 text-xs">هیچ نرخێک تۆمار نەکراوە.</div>
                    @endforelse
                </div>
            </div>
        </div>


        {{-- ٢. کارتی باکەپ و پاراستنی داتابەیس --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="size-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">💾</span>
                    <h3 class="font-bold text-slate-900 text-xs">باکەپی داتابەیس (.sql)</h3>
                </div>

                <form method="POST" action="{{ route('settings.backup') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary !py-1 !px-3 text-xs font-bold cursor-pointer">
                        + باکەپی نوێ
                    </button>
                </form>
            </div>

            <p class="text-[11px] text-slate-500 mb-3 leading-relaxed">
                باکەپەکان لە بوخچەی <code class="font-mono text-slate-800 bg-slate-100 px-1 py-0.5 rounded text-[10px]">storage/app/backups</code> هەڵدەگیرێن.
            </p>

            <div class="space-y-2 max-h-60 overflow-y-auto">
                @forelse ($backups as $backup)
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors text-xs">
                        <div class="min-w-0 flex-1 pl-2">
                            <div class="font-mono text-[11px] font-bold text-slate-900 truncate" title="{{ $backup['name'] }}">
                                {{ $backup['name'] }}
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-slate-500 num mt-0.5">
                                <span>{{ fmt_num($backup['size'] / 1024) }} KB</span>
                                <span>•</span>
                                <span>{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['created_at'])->format('Y/m/d H:i') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="{{ route('settings.backup.download', $backup['name']) }}"
                               class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors">
                                داگرتن
                            </a>

                            <form method="POST" action="{{ route('settings.backup.delete', $backup['name']) }}" onsubmit="return confirm('ئایا دڵنیایت لە سڕینەوەی ئەم باکەپە؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer" title="سڕینەوە">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">
                        هێشتا هیچ باکەپێک دروست نەکراوە.
                    </div>
                @endforelse
            </div>
        </div>


        {{-- ٣. کارتی زانیاری و تەندروستی سیستم --}}
        @if (isset($systemInfo))
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-3">
                    <span class="size-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs">⚡</span>
                    <h3 class="font-bold text-slate-900 text-xs">تەندروستی و زانیاری سیستم</h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">وەشانی PHP:</span>
                        <span class="num font-bold text-slate-800">{{ $systemInfo['php_version'] }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">وەشانی لاڕاڤێڵ:</span>
                        <span class="num font-bold text-slate-800">Laravel {{ $systemInfo['laravel_version'] }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">قەبارەی داتابەیس:</span>
                        <span class="num font-bold text-emerald-700">{{ $systemInfo['db_size'] }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">کاتی سێرڤەر:</span>
                        <span class="num font-bold text-slate-800" dir="ltr">{{ $systemInfo['server_time'] }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">دۆخی سێرڤەر:</span>
                        <span class="font-bold text-emerald-600 inline-flex items-center gap-1">
                            <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            چالاک و خێرا
                        </span>
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>

@endsection
