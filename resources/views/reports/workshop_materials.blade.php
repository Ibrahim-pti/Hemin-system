@extends('layouts.app')
@section('title', 'ڕاپۆرتی مەوادی خاو و کۆگا')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشانی فەرمی ڕاپۆرت --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-2xl shadow-md shadow-orange-500/20 shrink-0">
                🧱
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">ڕاپۆرتی مەوادی خاو و کۆگا</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200/80">
                        مەسروفیات و جوڵەی مەخزەن
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    تۆماری سەرفیاتی مەوادی خاو بۆ دروستکردن و هاتنی کەلوپەل لە کارگە
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('warehouses.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 inline-flex items-center gap-1.5 transition-all">
                <span>🏭</span>
                <span>پەیجی کۆگا</span>
            </a>
        </div>
    </div>

    {{-- پاڵاوتنی بەروار --}}
    @include('reports._filter')

    {{-- ٢. کارتە ئامارییەکان --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-rose-50/70 rounded-2xl p-4 border border-rose-200/80 shadow-xs">
            <div class="text-xs font-bold text-rose-800 mb-1 flex items-center gap-1.5">
                <span>📤</span>
                <span>سەرفیات (بەکارهاتوو لە دروستکردن)</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-rose-800 font-mono">{{ fmt_num($consumedCount) }} <span class="text-xs font-bold font-sans">جار</span></div>
        </div>

        <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <div class="text-xs font-bold text-emerald-800 mb-1 flex items-center gap-1.5">
                <span>📥</span>
                <span>هاتوو (زیادکراو بۆ مەخزەن)</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-800 font-mono">{{ fmt_num($receivedCount) }} <span class="text-xs font-bold font-sans">جار</span></div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="text-xs font-bold text-slate-500 mb-1 flex items-center gap-1.5">
                <span>📦</span>
                <span>مەوادی بەردەست لە کارگە</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">{{ fmt_num($materials->count()) }} <span class="text-xs font-bold font-sans text-slate-500">جۆر</span></div>
        </div>

        <div class="bg-amber-50/70 rounded-2xl p-4 border border-amber-200/80 shadow-xs">
            <div class="text-xs font-bold text-amber-800 mb-1 flex items-center gap-1.5">
                <span>⚠️</span>
                <span>مەوادە کەمبووەکان</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-800 font-mono">{{ fmt_num($materials->filter(fn($m) => $m->is_low)->count()) }} <span class="text-xs font-bold font-sans">مەواد</span></div>
        </div>
    </div>

    {{-- ٣. پوختەی سەرفیات بەپێی مەواد --}}
    @if($consumedByMaterial->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">🧱</span>
                    <h3 class="font-black text-sm text-slate-800">پوختەی مەوادە بەکارهاتووەکان (سەرفیات) بۆ دروستکردن</h3>
                </div>
                <span class="text-xs font-bold text-slate-400">لە ماوەی دیاریکراودا</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                        <tr>
                            <th class="p-3.5 w-12 text-center">#</th>
                            <th class="p-3.5">ناوی مەواد</th>
                            <th class="p-3.5 text-center">جارەکانی سەرفکردن</th>
                            <th class="p-3.5 text-left">کۆی بڕی بەکارهاتوو</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($consumedByMaterial as $index => $row)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 text-center font-mono font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="p-3.5 font-black text-slate-900">{{ $row['item_name'] }}</td>
                                <td class="p-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 font-mono font-bold text-slate-700">
                                        {{ fmt_num($row['count']) }} جار
                                    </span>
                                </td>
                                <td class="p-3.5 text-left font-mono font-black text-rose-600">
                                    {{ fmt_num($row['qty']) }} {{ $row['unit_name'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ٤. خشتەی هەموو جووڵەکانی مەخزەن لەم ماوەیەدا --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-base">📋</span>
                <h3 class="font-black text-sm text-slate-800">تۆماری جووڵەکان (بەکارهێنان و هاتنی مەواد)</h3>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200/80 text-slate-700 font-mono">
                کۆگای: {{ $workshopWarehouse?->name ?? 'کۆگای کارگە' }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5">بەروار</th>
                        <th class="p-3.5">مەواد</th>
                        <th class="p-3.5 text-center">جۆری جووڵە</th>
                        <th class="p-3.5 text-center">بڕ</th>
                        <th class="p-3.5">بۆ وەسڵی داواکاری</th>
                        <th class="p-3.5">تێبینی / هۆکار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 font-mono text-slate-600 whitespace-nowrap">{{ fmt_date($m->moved_at) }}</td>
                            <td class="p-3.5 font-black text-slate-900">{{ $m->item?->name ?? 'نەناسراو' }}</td>
                            <td class="p-3.5 text-center">
                                @if($m->direction === 'out')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        سەرفیات 📤
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        هاتوو 📥
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center font-mono font-black text-xs {{ $m->direction === 'out' ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $m->direction === 'out' ? '-' : '+' }}{{ fmt_num($m->qty) }} {{ $m->item?->unit?->name }}
                            </td>
                            <td class="p-3.5">
                                @php
                                    $refOrder = $m->reference instanceof \App\Models\Order ? $m->reference : null;
                                @endphp
                                @if($refOrder)
                                    <a href="{{ route('orders.show', $refOrder) }}" class="font-mono text-xs font-bold text-indigo-600 hover:underline">
                                        وەسڵی #{{ $refOrder->id }} ({{ $refOrder->customer?->name ?: 'کڕیار' }})
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-500 font-medium">{{ $m->reason ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-xs text-slate-400 font-medium">
                                هیچ جووڵەیەکی مەخزەن لەم ماوەیەدا تۆمار نەکراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
