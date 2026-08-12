@extends('layouts.app')
@section('title', 'راپۆرتەکان')

@section('content')

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($reports as $key => [$name, $description])
        <a href="{{ route('reports.show', $key) }}" class="card block transition-colors hover:bg-[--color-canvas]">
            <div class="card-body">
                <div class="font-semibold text-[--color-brand-700]">{{ $name }}</div>
                <p class="mt-1 text-sm text-[--color-ink-soft]">{{ $description }}</p>
            </div>
        </a>
    @endforeach

    <a href="{{ route('debts.index') }}" class="card block transition-colors hover:bg-[--color-canvas]">
        <div class="card-body">
            <div class="font-semibold text-[--color-brand-700]">قەرزەکان</div>
            <p class="mt-1 text-sm text-[--color-ink-soft]">قەرزی کڕیاران و فرۆشیاران بە تەمەن</p>
        </div>
    </a>

    <a href="{{ route('attendance.wages') }}" class="card block transition-colors hover:bg-[--color-canvas]">
        <div class="card-body">
            <div class="font-semibold text-[--color-brand-700]">حەقدەستەکان</div>
            <p class="mt-1 text-sm text-[--color-ink-soft]">ئامادەبوون و حەقدەستی کارمەندان</p>
        </div>
    </a>
</div>

@endsection
