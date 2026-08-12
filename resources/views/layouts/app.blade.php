<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'داشبۆرد') — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen" x-data="{ menuOpen: false }">

    {{-- ── پەردەی پشتەوە بۆ مۆبایل ── --}}
    <div x-show="menuOpen" x-cloak @click="menuOpen = false"
         class="fixed inset-0 z-30 bg-black/40 lg:hidden no-print"></div>

    {{-- ── مێنیوی لای ڕاست ── --}}
    <aside x-cloak
           :class="menuOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
           class="fixed top-0 right-0 z-40 h-screen w-64 overflow-y-auto border-l border-[--color-line] bg-white transition-transform duration-200 no-print">
        @include('partials.sidebar')
    </aside>

    {{-- ── ناوەڕۆک ── --}}
    <div class="lg:mr-64">

        <header class="sticky top-0 z-20 border-b border-[--color-line] bg-white no-print">
            <div class="flex items-center gap-3 px-4 py-3">
                <button @click="menuOpen = true" class="btn btn-ghost !p-2 lg:hidden" aria-label="مێنیو">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <h1 class="flex-1 truncate text-base font-semibold">@yield('title', 'داشبۆرد')</h1>

                @yield('actions')

                {{-- حاسیبە --}}
                <button @click="$dispatch('open-calculator')" class="btn btn-ghost !p-2" title="حاسیبە">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="4" y="3" width="16" height="18" rx="2"/>
                        <path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="p-4">
            @if (session('ok'))
                <div class="card mb-4 border-r-4 !border-r-[--color-ok] px-4 py-3 text-sm no-print">
                    {{ session('ok') }}
                </div>
            @endif

            @if (session('err'))
                <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm no-print">
                    {{ session('err') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @include('partials.calculator')
</body>
</html>
