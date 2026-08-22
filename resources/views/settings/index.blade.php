@extends('layouts.app')
@section('title', 'ڕێکخستن و باکەپ')

@section('content')

<div class="grid gap-4 lg:grid-cols-2">

    {{-- زانیاری کارگە --}}
    <form method="POST" action="{{ route('settings.update') }}" class="card">
        @csrf @method('PUT')
        <div class="card-head">زانیاری کارگە</div>
        <div class="card-body space-y-4">
            <p class="text-xs text-[--color-ink-soft]">ئەمانە لەسەر وەسڵ و حەقدی چاپ دەکرێن.</p>

            <div>
                <label class="label" for="company_name">ناوی کارگە <span class="text-[--color-danger]">*</span></label>
                <input id="company_name" name="company_name" class="field" required
                       value="{{ old('company_name', $settings['company_name'] ?? '') }}">
            </div>

            <div>
                <label class="label" for="company_tagline">دەقی ژێر ناو</label>
                <input id="company_tagline" name="company_tagline" class="field"
                       value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="company_phone">تەلەفۆن</label>
                    <input id="company_phone" name="company_phone" class="field num"
                           value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                </div>
                <div>
                    <label class="label" for="company_phone2">تەلەفۆنی دووەم</label>
                    <input id="company_phone2" name="company_phone2" class="field num"
                           value="{{ old('company_phone2', $settings['company_phone2'] ?? '') }}">
                </div>
            </div>

            <div>
                <label class="label" for="company_address">ناونیشان</label>
                <input id="company_address" name="company_address" class="field"
                       value="{{ old('company_address', $settings['company_address'] ?? '') }}">
            </div>

            <div>
                <label class="label" for="invoice_footer">دەقی ژێر وەسڵ</label>
                <input id="invoice_footer" name="invoice_footer" class="field"
                       value="{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}">
            </div>

            <div>
                <label class="label" for="xeiqd_api_key">کلیل (Token)ی XEIQD API</label>
                <input id="xeiqd_api_key" name="xeiqd_api_key" class="field num text-xs" dir="ltr"
                       placeholder="Bearer Token لێرە دابنێ..."
                       value="{{ old('xeiqd_api_key', $settings['xeiqd_api_key'] ?? '') }}">
                <p class="mt-1 text-xs text-[--color-ink-soft]">
                    بۆ وەرگرتنی نوێترین نرخی هەولێر لە <a href="https://xeiqd.com/profile" target="_blank" class="text-blue-600 underline">xeiqd.com/profile</a>.
                </p>
            </div>

            <button class="btn btn-primary">پاشەکەوتکردن</button>
        </div>
    </form>

    <div class="space-y-4">

        {{-- نرخی دۆلار --}}
        <div class="card">
            <div class="card-head flex items-center justify-between">
                <span>نرخی دۆلار</span>
                <div class="flex items-center gap-2">
                    <span class="num font-semibold">{{ fmt_num($currentRate) }} د.ع</span>
                    <form method="POST" action="{{ route('settings.sync-rate') }}">
                        @csrf
                        <button class="btn btn-ghost !py-1 !px-2 text-xs text-blue-600 hover:bg-blue-50 border border-blue-200" title="وەرگرتنی نرخی ئەمڕۆ لە ئینتەرنێت">
                            🔄 وەرگرتن لە API
                        </button>
                    </form>
                </div>
            </div>
            <form method="POST" action="{{ route('settings.rate') }}">
                @csrf
                <div class="card-body grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="label">بەروار</label>
                        <input type="date" name="effective_date" class="field num" required
                               value="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="label">١ دۆلار = ؟ دینار</label>
                        <input type="number" step="0.01" min="1" name="usd_to_iqd" class="field num" required
                               value="{{ $currentRate }}">
                    </div>
                    <div class="flex items-end">
                        <button class="btn btn-primary w-full">تۆمارکردن</button>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto border-t border-[--color-line]">
                <table class="table">
                    <thead><tr><th>بەروار</th><th class="num">نرخ</th></tr></thead>
                    <tbody>
                        @forelse ($rates as $rate)
                            <tr>
                                <td class="num">{{ fmt_date($rate->effective_date) }}</td>
                                <td class="num">{{ fmt_num($rate->usd_to_iqd) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-sm text-[--color-ink-soft]">هیچ نرخێک نییە.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[--color-line] px-4 py-2 text-xs text-[--color-ink-soft]">
                گۆڕینی نرخ کاریگەری لەسەر مامەڵەی پێشوو نییە — هەریەکەیان نرخی ڕۆژی خۆیان هەڵگرتووە.
            </div>
        </div>

        {{-- باکەپ --}}
        <div class="card">
            <div class="card-head flex items-center justify-between">
                <span>باکەپی داتابەیس</span>
                <form method="POST" action="{{ route('settings.backup') }}">
                    @csrf
                    <button class="btn btn-primary !py-1">باکەپی نوێ</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>فایل</th><th class="num">قەبارە</th><th class="num">بەروار</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr>
                                <td class="num">{{ $backup['name'] }}</td>
                                <td class="num text-[--color-ink-soft]">{{ fmt_num($backup['size'] / 1024) }} KB</td>
                                <td class="num text-[--color-ink-soft]">
                                    {{ \Illuminate\Support\Carbon::createFromTimestamp($backup['created_at'])->format('Y/m/d H:i') }}
                                </td>
                                <td class="text-left">
                                    <a href="{{ route('settings.backup.download', $backup['name']) }}"
                                       class="text-sm text-[--color-brand-700]">داگرتن</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-sm text-[--color-ink-soft]">هێشتا باکەپ نییە.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[--color-line] px-4 py-2 text-xs text-[--color-ink-soft]">
                باکەپەکان لە <span class="num">storage/app/backups</span> هەڵدەگیرێن. پێشنیار: هەفتەیەک جارێک داگری بکە و لە شوێنێکی تر هەڵیبگرە.
            </div>
        </div>
    </div>
</div>

@endsection
