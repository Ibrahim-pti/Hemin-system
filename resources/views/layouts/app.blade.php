<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سەرەکی') — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">

    {{-- ── باری سەرەوە ── --}}
    <header class="topbar no-print" x-data="clock()">
        {{-- ڕاست: کاتژمێر و بەروار --}}
        <div class="flex items-center gap-3">
            <div class="flex size-8 items-center justify-center rounded bg-[--color-danger] text-xs font-bold text-white">
                هـ
            </div>
            <div class="num text-sm" x-text="time"></div>
            <div class="num hidden text-sm text-white/70 sm:block" x-text="date"></div>
        </div>

        {{-- ناوەڕاست: بەکارهێنەر --}}
        <div class="flex items-center gap-2 text-sm">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/>
            </svg>
            <span>{{ auth()->user()->name }}</span>
        </div>

        {{-- چەپ: زانیاری کارگە --}}
        <div class="hidden items-center gap-2 text-sm md:flex">
            <span>{{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</span>
            <span class="num text-white/70" dir="ltr">{{ \App\Models\Setting::get('company_phone') }}</span>
        </div>
    </header>

    {{-- ── باری ناونیشان ── --}}
    <div class="no-print flex flex-wrap items-center gap-2 border-b border-[--color-line] bg-[--color-surface] px-4 py-2.5">
        @if (! request()->routeIs('dashboard'))
            <a href="{{ route('dashboard') }}" class="btn btn-ghost !px-2.5 !py-1.5" title="سەرەکی">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z"/>
                </svg>
            </a>
        @endif

        <h1 class="flex-1 truncate text-base font-semibold">@yield('title', 'سەرەکی')</h1>

        @yield('actions')

        <button @click="$dispatch('open-calculator')" class="btn btn-ghost !px-2.5 !py-1.5" title="حاسیبە">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                <rect x="4" y="3" width="16" height="18" rx="2"/>
                <path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01"/>
            </svg>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-ghost !px-2.5 !py-1.5" title="دەرچوون">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 17l5-5-5-5M20 12H9M12 3H6a1 1 0 00-1 1v16a1 1 0 001 1h6"/>
                </svg>
            </button>
        </form>
    </div>

    {{-- ── ناوەڕۆک ── --}}
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

    <script>
    // کاتژمێری زیندووی باری سەرەوە.
    function clock() {
        return {
            time: '',
            date: '',
            init() {
                const tick = () => {
                    const now = new Date();
                    this.time = now.toLocaleTimeString('en-GB', { hour12: false });
                    this.date = now.toLocaleDateString('en-CA');
                };
                tick();
                setInterval(tick, 1000);
            },
        }
    }
    </script>

    {{-- سکریپتەکانی لاپەڕە پێش Alpine (کە لەگەڵ Livewire دێت) بارببن. --}}
    @stack('scripts')
    @livewireScripts
</body>
</html>
