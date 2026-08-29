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

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($reports as $key => [$name, $description])
        <a href="{{ route('reports.show', $key) }}"
           class="card block transition-colors hover:bg-[--color-surface-soft]">
            <div class="card-body flex items-start gap-3">
                <span class="icon-chip {{ $icons[$key][1] ?? '' }}">
                    @include('partials.icon', ['name' => $icons[$key][0] ?? 'reports', 'class' => 'size-5'])
                </span>
                <div class="min-w-0">
                    <div class="font-semibold">{{ $name }}</div>
                    <p class="mt-0.5 text-sm text-[--color-ink-soft]">{{ $description }}</p>
                </div>
            </div>
        </a>
    @endforeach

    <a href="{{ route('debts.index') }}" class="card block transition-colors hover:bg-[--color-surface-soft]">
        <div class="card-body flex items-start gap-3">
            <span class="icon-chip icon-chip-danger">
                @include('partials.icon', ['name' => 'debts', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="font-semibold">قەرزەکان</div>
                <p class="mt-0.5 text-sm text-[--color-ink-soft]">قەرزی کڕیاران و فرۆشیاران بە تەمەن</p>
            </div>
        </div>
    </a>

    <a href="{{ route('attendance.wages') }}" class="card block transition-colors hover:bg-[--color-surface-soft]">
        <div class="card-body flex items-start gap-3">
            <span class="icon-chip icon-chip-warn">
                @include('partials.icon', ['name' => 'employees', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="font-semibold">حەقدەستەکان</div>
                <p class="mt-0.5 text-sm text-[--color-ink-soft]">ئامادەبوون و حەقدەستی کارمەندان</p>
            </div>
        </div>
    </a>
</div>

@endsection
