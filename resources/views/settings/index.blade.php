@extends('layouts.app')
@section('title', 'ڕێکخستن و باکەپ')

@section('content')

{{-- سەردێڕی پەڕە --}}
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-xl font-black text-slate-900">⚙️ ڕێکخستنەکانی سیستم و هەژمارەکان</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">بەڕێوەبردنی زانیاری کارگە، وەسڵ، کاتەکانی دەوام، هەژماری بەڕێوەبەر و وەستا، و باکەپی داتابەیس</p>
    </div>

    {{-- دوگمەی خاوێنکردنەوەی خێرای کاش --}}
    <form method="POST" action="{{ route('settings.clear-cache') }}">
        @csrf
        <button type="submit" class="btn btn-ghost !py-1.5 !px-3 text-xs bg-white border border-slate-200 shadow-2xs hover:bg-slate-50 text-slate-700 font-bold inline-flex items-center gap-1.5 cursor-pointer">
            🧹 خاوێنکردنەوەی کاش
        </button>
    </form>
</div>

<div class="grid gap-6 lg:grid-cols-2">

    {{-- ستوونی ڕاست: زانیاری کارگە، وەسڵ و دەوام --}}
    <div class="space-y-6">

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
                        <h2 class="font-bold text-slate-900 text-sm">ڕێکخستنی دەوام و کارگە</h2>
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

            {{-- دوگمەی پاشەکەوتکردنی گشتی --}}
            <div class="flex justify-end pt-1">
                <button type="submit" class="btn btn-primary !py-2.5 !px-8 text-sm font-black shadow-md cursor-pointer">
                    💾 پاشەکەوتکردنی ڕێکخستنەکان
                </button>
            </div>
        </form>

    </div>


    {{-- ستوونی چەپ: هەژماری بەڕێوەبەر، هەژماری وەستا، و باکەپی داتابەیس --}}
    <div class="space-y-6">

        {{-- ١. هەژماری بەڕێوەبەر (ئیمەیڵ و پاسۆرد) --}}
        @if ($adminUser)
            <div class="rounded-2xl border border-blue-200/80 bg-linear-to-br from-blue-50/40 to-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2.5 border-b border-blue-100 pb-3 mb-4">
                    <span class="size-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm">👑</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">هەژماری بەڕێوەبەر (ئیمەیڵ و پاسۆرد)</h2>
                        <p class="text-[11px] text-slate-500">گۆڕینی ناوی بەکارهێنەر، ئیمەیڵی چوونەژوورەوە، و وشەی نهێنی.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.users.update', $adminUser) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label text-xs">ناوی بەڕێوەبەر <span class="text-rose-500">*</span></label>
                            <input name="name" class="field font-bold text-xs" required
                                   value="{{ old('name', $adminUser->name) }}">
                        </div>

                        <div>
                            <label class="label text-xs">ئیمەیڵی چوونەژوورەوە <span class="text-rose-500">*</span></label>
                            <input name="email" type="email" class="field font-mono text-xs" dir="ltr" required
                                   value="{{ old('email', $adminUser->email) }}">
                        </div>
                    </div>

                    <div>
                        <label class="label text-xs">ژمارەی مۆبایل</label>
                        <input name="phone" class="field num text-xs" dir="ltr"
                               placeholder="0750xxxxxxx"
                               value="{{ old('phone', $adminUser->phone) }}">
                    </div>

                    <div class="border-t border-blue-100/60 pt-3">
                        <div class="text-[11px] font-bold text-blue-900 mb-2">گۆڕینی وشەی نهێنی (ئەگەر ناتەوێ بیگۆڕی، بە بەتاڵی جێی بهێڵە):</div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="label text-[11px]">وشەی نهێنی نوێ</label>
                                <input name="password" type="password" class="field text-xs font-mono" dir="ltr"
                                       placeholder="••••••••">
                            </div>
                            <div>
                                <label class="label text-[11px]">دووپاتکردنەوەی وشەی نهێنی</label>
                                <input name="password_confirmation" type="password" class="field text-xs font-mono" dir="ltr"
                                       placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="btn btn-primary !py-2 !px-5 text-xs font-bold shadow-xs cursor-pointer">
                            نوێکردنەوەی هەژماری بەڕێوەبەر
                        </button>
                    </div>
                </form>
            </div>
        @endif


        {{-- ٢. هەژماری وەستا / بەرپرسی کارگە (ئیمەیڵ و پاسۆرد) --}}
        @if ($workshopUser)
            <div class="rounded-2xl border border-amber-200/80 bg-linear-to-br from-amber-50/40 to-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2.5 border-b border-amber-100 pb-3 mb-4">
                    <span class="size-8 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-sm">👷</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">هەژماری وەستا (ئیمەیڵ و پاسۆرد)</h2>
                        <p class="text-[11px] text-slate-500">گۆڕینی ئیمەیڵ و وشەی نهێنی بەرپرسی کارگە و مەخزەن.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.users.update', $workshopUser) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label text-xs">ناوی وەستا / بەرپرسی کارگە <span class="text-rose-500">*</span></label>
                            <input name="name" class="field font-bold text-xs" required
                                   value="{{ old('name', $workshopUser->name) }}">
                        </div>

                        <div>
                            <label class="label text-xs">ئیمەیڵی چوونەژوورەوە <span class="text-rose-500">*</span></label>
                            <input name="email" type="email" class="field font-mono text-xs" dir="ltr" required
                                   value="{{ old('email', $workshopUser->email) }}">
                        </div>
                    </div>

                    <div>
                        <label class="label text-xs">ژمارەی مۆبایل</label>
                        <input name="phone" class="field num text-xs" dir="ltr"
                               placeholder="0750xxxxxxx"
                               value="{{ old('phone', $workshopUser->phone) }}">
                    </div>

                    <div class="border-t border-amber-100/60 pt-3">
                        <div class="text-[11px] font-bold text-amber-900 mb-2">گۆڕینی وشەی نهێنی (ئەگەر ناتەوێ بیگۆڕی، بە بەتاڵی جێی بهێڵە):</div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="label text-[11px]">وشەی نهێنی نوێ</label>
                                <input name="password" type="password" class="field text-xs font-mono" dir="ltr"
                                       placeholder="••••••••">
                            </div>
                            <div>
                                <label class="label text-[11px]">دووپاتکردنەوەی وشەی نهێنی</label>
                                <input name="password_confirmation" type="password" class="field text-xs font-mono" dir="ltr"
                                       placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="btn btn-primary !py-2 !px-5 text-xs font-bold shadow-xs !bg-amber-600 hover:!bg-amber-700 border-none cursor-pointer">
                            نوێکردنەوەی هەژماری وەستا
                        </button>
                    </div>
                </form>
            </div>
        @endif


        {{-- ٣. باکەپی داتابەیس --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="size-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">💾</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">باکەپی داتابەیس (.sql)</h2>
                        <p class="text-[11px] text-slate-500">پاراستنی داتا و دروستکردنی کۆپی یەدەگ.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.backup') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary !py-1.5 !px-3.5 text-xs font-bold cursor-pointer">
                        + دروستکردنی باکەپ
                    </button>
                </form>
            </div>

            <p class="text-[11px] text-slate-500 mb-3 leading-relaxed">
                باکەپەکان لە بوخچەی <code class="font-mono text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">storage/app/backups</code> هەڵدەگیرێن.
            </p>

            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse ($backups as $backup)
                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors text-xs">
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
                               class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors">
                                داگرتن
                            </a>

                            <form method="POST" action="{{ route('settings.backup.delete', $backup['name']) }}" onsubmit="return confirm('ئایا دڵنیایت لە سڕینەوەی ئەم باکەپە؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer" title="سڕینەوە">
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

    </div>

</div>

@endsection
