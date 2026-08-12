<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>سەرەکی — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
{{-- لایەقی مێنیوی سەرەکی — ناوی کارگە و کاتژمێر لە سەرەوە، پاشان کارتەکان. --}}
<body class="min-h-screen">

    {{-- ── سەرەوەی کارتەکان ── --}}
    <header class="menu-head" x-data="clock()">

        {{-- ڕاست: کاتژمێر و بەروار --}}
        <div>
            <div class="clock-time" dir="ltr" style="text-align: right" x-text="time"></div>
            <div class="clock-date" x-text="date"></div>
        </div>

        {{-- ناوەڕاست: ناوی کارگە --}}
        <div class="menu-title">{{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</div>

        {{-- چەپ: بەکارهێنەر و دوگمەکان --}}
        <div class="flex items-center justify-end gap-2">
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

            <div class="hidden text-left leading-tight sm:block">
                <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
                <div class="text-xs text-[--color-ink-soft]">
                    {{ auth()->user()->isAdmin() ? 'بەڕێوەبەر' : 'بەرپرسی کۆگا' }}
                </div>
            </div>
            <div class="footbar-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
        </div>
    </header>

    <main class="p-4 pt-3">
        @if (session('ok'))
            <div class="mb-3 flex items-center gap-2.5 rounded-[--radius-card] border border-[--color-ok]/25 bg-[--color-ok-soft] px-4 py-3 text-sm text-[--color-ok]">
                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>
                </svg>
                <span class="text-[--color-ink]">{{ session('ok') }}</span>
            </div>
        @endif

        @if (session('err'))
            <div class="mb-3 flex items-center gap-2.5 rounded-[--radius-card] border border-[--color-danger]/25 bg-[--color-danger-soft] px-4 py-3 text-sm text-[--color-danger]">
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
    // کاتژمێری زیندووی سەرەوەی کارتەکان.
    function clock() {
        const days = ['یەکشەممە', 'دووشەممە', 'سێشەممە', 'چوارشەممە', 'پێنجشەممە', 'هەینی', 'شەممە'];

        return {
            time: '',
            date: '',
            init() {
                const tick = () => {
                    const now = new Date();
                    const pad = (n) => String(n).padStart(2, '0');

                    this.time = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                    this.date = `${days[now.getDay()]} · ${now.getFullYear()}/${pad(now.getMonth() + 1)}/${pad(now.getDate())}`;
                };
                tick();
                setInterval(tick, 1000);
            },
        }
    }
    </script>

    @stack('scripts')
    @livewireScripts
</body>
</html>
