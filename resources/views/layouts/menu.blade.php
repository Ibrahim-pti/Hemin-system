<!DOCTYPE html>
<html lang="ckb" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>داشبۆرد — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('sidebar_open') === 'false') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    <style>
        html.sidebar-collapsed aside.sidebar-nav {
            width: 5rem !important;
            min-width: 5rem !important;
        }
        html.sidebar-collapsed aside.sidebar-nav [x-show="sidebarOpen"] {
            display: none !important;
        }
        .sidebar-no-transition, .sidebar-no-transition * {
            transition: none !important;
        }
        .sidebar-link:not(.active-link):hover {
            background: rgba(255, 255, 255, 0.06) !important;
            color: #f8fafc !important;
        }
        .sidebar-scroll {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .sidebar-scroll::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased font-sans"
      x-data="{
          sidebarOpen: localStorage.getItem('sidebar_open') !== 'false',
          mobileOpen: false,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              localStorage.setItem('sidebar_open', this.sidebarOpen);
              document.documentElement.classList.toggle('sidebar-collapsed', !this.sidebarOpen);
          },
          clock: typeof clock === 'function' ? clock() : null
      }">

    <div style="display: flex; height: 100vh; width: 100%; overflow: hidden;">

        {{-- ── باکدراپی مۆبایل ── --}}
        <div x-show="mobileOpen"
             x-cloak
             @click="mobileOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="position: fixed; inset: 0; z-index: 40; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px);"></div>

        {{-- ── مێنیوی سەرەکی (Sidebar) ── --}}
        @include('partials.sidebar')

        {{-- ── بەشی سەرەکی و ناوەڕۆک (Main Content Area) ── --}}
        <div style="display: flex; flex: 1; flex-direction: column; overflow: hidden; min-width: 0; background: #f1f5f9;">

            {{-- هێڵی سەرەوە (Top Header Bar) --}}
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200/80 bg-white px-4 md:px-6 shadow-2xs">
                {{-- دوگمەی تەنیشت و کاتژمێر --}}
                <div class="flex items-center gap-3">
                    <button type="button"
                            @click="if (window.innerWidth < 640) { mobileOpen = !mobileOpen } else { toggleSidebar() }"
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-700 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all cursor-pointer active:scale-95"
                            title="کردنەوە / داخستنی مێنیو">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <div x-data="{
                        time: '',
                        date: '',
                        init() {
                            const tick = () => {
                                const now = new Date();
                                const days = ['یەکشەممە', 'دووشەممە', 'سێشەممە', 'چوارشەممە', 'پێنجشەممە', 'هەینی', 'شەممە'];
                                const pad = (n) => String(n).padStart(2, '0');
                                let h = now.getHours();
                                const m = pad(now.getMinutes());
                                const s = pad(now.getSeconds());
                                const ampm = h >= 12 ? 'PM' : 'AM';
                                h = h % 12 || 12;
                                this.time = `${pad(h)}:${m}:${s} ${ampm}`;
                                this.date = `${days[now.getDay()]} · ${now.getFullYear()}/${pad(now.getMonth() + 1)}/${pad(now.getDate())}`;
                            };
                            tick();
                            setInterval(tick, 1000);
                        }
                    }" class="hidden sm:block">
                        <div class="clock-time font-bold text-sm text-slate-800" dir="ltr" style="text-align: right" x-text="time"></div>
                        <div class="clock-date text-[11px] text-slate-500" x-text="date"></div>
                    </div>
                </div>

                {{-- لای چەپ: بەکارهێنەر، حاسیبە و دەرچوون --}}
                <div class="flex items-center gap-2">
                    {{-- بەکارهێنەر --}}
                    <div class="flex items-center gap-2 rounded-xl border border-slate-200/80 bg-slate-50/80 px-2.5 py-1.5 text-slate-700">
                        <div class="flex size-6 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 font-bold text-xs border border-blue-500/20">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                    </div>

                    <button @click="$dispatch('open-calculator')" class="btn btn-ghost !px-3 !py-1.5 text-xs text-slate-600 hover:bg-slate-100" title="حاسیبە">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                            <rect x="4" y="3" width="16" height="18" rx="2"/>
                            <path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01"/>
                        </svg>
                        <span class="mr-1.5">حاسیبە</span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-ghost !px-3 !py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="دەرچوون">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 17l5-5-5-5M20 12H9M12 3H6a1 1 0 00-1 1v16a1 1 0 001 1h6"/>
                            </svg>
                            <span class="mr-1.5">دەرچوون</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- ناوەڕۆکی لاپەڕەی داشبۆرد --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                @if (session('ok'))
                    <div class="mb-4 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <svg class="size-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>
                        </svg>
                        <span>{{ session('ok') }}</span>
                    </div>
                @endif

                @if (session('err'))
                    <div class="mb-4 flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <svg class="size-5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>
                        </svg>
                        <span>{{ session('err') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>

    </div>

    @include('partials.calculator')

    <script>
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
</body>
</html>
