<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سیستەم') — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">

    {{-- ── باری سووکی لاپەڕە — دوگمەی «سەرەکی» + ناونیشان ── --}}
    <div class="no-print flex flex-wrap items-center gap-2 px-4 pt-4">
        <a href="{{ route('dashboard') }}" class="home-card">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z"/>
            </svg>
            سەرەکی
        </a>

        <h1 class="flex-1 truncate text-lg font-bold">@yield('title')</h1>

        @yield('actions')

        <button @click="$dispatch('open-calculator')" class="btn btn-ghost !px-2.5 !py-1.5" title="حاسیبە" x-data>
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                <rect x="4" y="3" width="16" height="18" rx="2"/>
                <path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01"/>
            </svg>
        </button>
    </div>

    <main class="p-4">
        @if (session('ok'))
            <div class="no-print mb-4 flex items-center gap-2.5 rounded-[--radius-card] border border-[--color-ok]/25 bg-[--color-ok-soft] px-4 py-3 text-sm text-[--color-ok]">
                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>
                </svg>
                <span class="text-[--color-ink]">{{ session('ok') }}</span>
            </div>
        @endif

        @if (session('err'))
            <div class="no-print mb-4 flex items-center gap-2.5 rounded-[--radius-card] border border-[--color-danger]/25 bg-[--color-danger-soft] px-4 py-3 text-sm text-[--color-danger]">
                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>
                </svg>
                <span class="text-[--color-ink]">{{ session('err') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.calculator')

    @stack('scripts')
    @livewireScripts
</body>
</html>
