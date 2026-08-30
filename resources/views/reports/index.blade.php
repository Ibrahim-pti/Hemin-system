@extends('layouts.app')
@section('title', 'راپۆرتەکان')

@section('content')

@php
    // هەر راپۆرتێک ئایکۆن و تۆنی خۆی هەیە بۆ ئەوەی بە یەک نیگا بناسرێتەوە.
    $icons = [
        'sales' => ['orders', ''],
        'purchases' => ['purchases', ''],
        'profit' => ['reports', 'icon-chip-ok'],
        'stock' => ['items', ''],
        'cash' => ['cash', ''],
        'workshop_production' => ['orders', 'icon-chip-brand'],
        'workshop_materials' => ['items', 'icon-chip-warn'],
    ];
@endphp

{{-- ئاماری گشتی سیستم --}}
@if (isset($stats))
    <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
        <div class="rounded-2xl border border-blue-100 bg-linear-to-br from-blue-50/70 to-white p-4 shadow-xs">
            <div class="text-xs font-bold text-blue-700">کۆی فرۆشتن</div>
            <div class="num mt-2 text-xl font-black text-slate-900">{{ fmt_money($stats['sales']) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">{{ fmt_num($stats['orders_count']) }} وەسڵ</div>
        </div>

        <div class="rounded-2xl border border-indigo-100 bg-linear-to-br from-indigo-50/70 to-white p-4 shadow-xs">
            <div class="text-xs font-bold text-indigo-700">کۆی کڕین</div>
            <div class="num mt-2 text-xl font-black text-slate-900">{{ fmt_money($stats['purchases']) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">مەواد و کەلوپەل</div>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-linear-to-br from-emerald-50/70 to-white p-4 shadow-xs">
            <div class="text-xs font-bold text-emerald-700">قازانجی صافی</div>
            <div class="num mt-2 text-xl font-black {{ $stats['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ fmt_money($stats['profit']) }}
            </div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">دوای دەرکردنی خەرجییەکان</div>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-linear-to-br from-amber-50/70 to-white p-4 shadow-xs">
            <div class="text-xs font-bold text-amber-700">بەهای مەخزەن</div>
            <div class="num mt-2 text-xl font-black text-slate-900">{{ fmt_money($stats['stock_value']) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">کاڵاکانی ناو کۆگا</div>
        </div>

        <div class="col-span-2 sm:col-span-1 rounded-2xl border border-rose-100 bg-linear-to-br from-rose-50/70 to-white p-4 shadow-xs">
            <div class="text-xs font-bold text-rose-700">قەرزی کڕیاران</div>
            <div class="num mt-2 text-xl font-black text-rose-700">{{ fmt_money($stats['debts']) }}</div>
            <div class="mt-1 text-[11px] text-slate-500 font-medium">ماوە لای کڕیارەکان</div>
        </div>
    </div>
@endif

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($reports as $key => [$name, $description])
        <a href="{{ route('reports.show', $key) }}"
           class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md block">
            <div class="flex items-start gap-3.5">
                <span class="icon-chip transition-transform group-hover:scale-110 {{ $icons[$key][1] ?? '' }}">
                    @include('partials.icon', ['name' => $icons[$key][0] ?? 'reports', 'class' => 'size-5'])
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $name }}</span>
                        <span class="text-slate-400 group-hover:translate-x-[-2px] group-hover:text-blue-600 transition-all text-xs">←</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $description }}</p>
                </div>
            </div>
        </a>
    @endforeach

    <a href="{{ route('debts.index') }}"
       class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:border-rose-300 hover:shadow-md block">
        <div class="flex items-start gap-3.5">
            <span class="icon-chip icon-chip-danger transition-transform group-hover:scale-110">
                @include('partials.icon', ['name' => 'debts', 'class' => 'size-5'])
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 group-hover:text-rose-600 transition-colors">قەرزەکان</span>
                    <span class="text-slate-400 group-hover:translate-x-[-2px] group-hover:text-rose-600 transition-all text-xs">←</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">قەرزی کڕیاران و فرۆشیاران بە تەمەن</p>
            </div>
        </div>
    </a>

    <a href="{{ route('attendance.wages') }}"
       class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md block">
        <div class="flex items-start gap-3.5">
            <span class="icon-chip icon-chip-warn transition-transform group-hover:scale-110">
                @include('partials.icon', ['name' => 'employees', 'class' => 'size-5'])
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 group-hover:text-amber-600 transition-colors">حەقدەستەکان</span>
                    <span class="text-slate-400 group-hover:translate-x-[-2px] group-hover:text-amber-600 transition-all text-xs">←</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">ئامادەبوون و حەقدەستی کارمەندان</p>
            </div>
        </div>
    </a>
</div>

@endsection
