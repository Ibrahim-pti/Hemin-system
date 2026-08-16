<!DOCTYPE html>
<html lang="ckb" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>داشبۆرد — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans"
      x-data="{
          sidebarOpen: true,
          mobileOpen: false,
          clock: clock()
      }">

    <div class="flex h-screen w-full overflow-hidden">

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
             class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs md:hidden"></div>

        {{-- ── مێنیوی تەنیشت (Sidebar Menu) ── --}}
        <aside :class="{
                   'translate-x-0': mobileOpen,
                   'translate-x-full md:translate-x-0': !mobileOpen,
                   'w-64 min-w-[16rem]': sidebarOpen,
                   'w-20 min-w-[5rem]': !sidebarOpen
               }"
               class="fixed inset-y-0 right-0 z-50 flex h-full flex-col border-l border-slate-200 bg-white shadow-sm transition-all duration-200 md:static md:z-auto shrink-0">

            {{-- سەری مێنیو: لۆگۆ و ناوی کارگە --}}
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-100 px-4">
                <div class="flex items-center gap-3 overflow-hidden">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white font-bold shadow-xs">
                        هـ
                    </span>
                    <div x-show="sidebarOpen" class="min-w-0 transition-opacity">
                        <div class="truncate text-sm font-bold text-slate-800">
                            {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}
                        </div>
                        <div class="truncate text-[11px] text-slate-400">سیستەمی بەڕێوەبردن</div>
                    </div>
                </div>

                {{-- داخستنی مۆبایل --}}
                <button @click="mobileOpen = false" class="text-slate-400 hover:text-slate-600 md:hidden">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- لیستی بەشەکانی دراوەر / مێنیو --}}
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-4">

                {{-- ١. کۆگا و لایەنەکان --}}
                <div>
                    <div x-show="sidebarOpen" class="px-2.5 mb-1.5 text-[11px] font-bold text-slate-400">
                        کۆگا و لایەنەکان
                    </div>
                    <nav class="space-y-1">
                        @if (auth()->user()->can('view_stock'))
                            <a href="{{ route('items.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-semibold group"
                               title="دۆخی کۆگا">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'items', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">دۆخی کۆگا</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_stock_counts'))
                            <a href="{{ route('counts.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-semibold group"
                               title="جەردی کۆگا">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'counts', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">جەردی کۆگا</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_items'))
                            <a href="{{ route('warehouses.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-semibold group"
                               title="کۆگاکان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'warehouses', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">کۆگاکان</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_customers'))
                            <a href="{{ route('customers.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-semibold group"
                               title="کڕیارەکان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'customers', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">کڕیارەکان</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_suppliers'))
                            <a href="{{ route('suppliers.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-semibold group"
                               title="فرۆشیارەکان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'suppliers', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">فرۆشیارەکان</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_employees'))
                            <a href="{{ route('employees.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-semibold group"
                               title="کارمەندان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'employees', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">کارمەندان</span>
                            </a>
                        @endif
                    </nav>
                </div>

                {{-- ٢. فرۆشتن، کڕین و دارایی --}}
                <div>
                    <div x-show="sidebarOpen" class="px-2.5 mb-1.5 text-[11px] font-bold text-slate-400">
                        فرۆشتن و دارایی
                    </div>
                    <nav class="space-y-1">
                        @if (auth()->user()->can('manage_orders'))
                            <a href="{{ route('orders.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-cyan-50 hover:text-cyan-800 transition-colors text-xs font-semibold group"
                               title="وەسڵ و داواکاری">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'orders', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">وەسڵ و داواکاری</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_purchases'))
                            <a href="{{ route('purchases.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-cyan-50 hover:text-cyan-800 transition-colors text-xs font-semibold group"
                               title="پسوولەی کڕین">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'purchases', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">پسوولەی کڕین</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_external_jobs'))
                            <a href="{{ route('external-jobs.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-amber-50 hover:text-amber-800 transition-colors text-xs font-semibold group"
                               title="ئیشی خاریجی">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'external-jobs', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">ئیشی خاریجی</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_cash'))
                            <a href="{{ route('cash.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-800 transition-colors text-xs font-semibold group"
                               title="قاسە">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'cash', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">قاسە</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_payments'))
                            <a href="{{ route('payments.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-800 transition-colors text-xs font-semibold group"
                               title="حەقدی و پارەدان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'payments', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">حەقدی و پارەدان</span>
                            </a>

                            <a href="{{ route('debts.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-800 transition-colors text-xs font-semibold group"
                               title="قەرزەکان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'debts', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">قەرزەکان</span>
                            </a>
                        @endif
                    </nav>
                </div>

                {{-- ٣. کارمەندان و ڕاپۆرت --}}
                <div>
                    <div x-show="sidebarOpen" class="px-2.5 mb-1.5 text-[11px] font-bold text-slate-400">
                        ڕاپۆرت و سیستەم
                    </div>
                    <nav class="space-y-1">
                        @if (auth()->user()->can('manage_employees'))
                            <a href="{{ route('attendance.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors text-xs font-semibold group"
                               title="هاتن و چوون">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'attendance', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">هاتن و چوون</span>
                            </a>

                            <a href="{{ route('attendance.wages') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors text-xs font-semibold group"
                               title="حەقدەستەکان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'employees', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">حەقدەستەکان</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('view_reports'))
                            <a href="{{ route('reports.show', 'profit') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-800 transition-colors text-xs font-semibold group"
                               title="راپۆرتی قازانج">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'reports', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">راپۆرتی قازانج</span>
                            </a>

                            <a href="{{ route('reports.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-800 transition-colors text-xs font-semibold group"
                               title="هەموو راپۆرت">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'reports', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">هەموو راپۆرت</span>
                            </a>
                        @endif

                        @if (auth()->user()->can('manage_settings'))
                            <a href="{{ route('activity.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors text-xs font-semibold group"
                               title="مێژووی کردارەکان">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-slate-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'activity', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">مێژووی کردارەکان</span>
                            </a>

                            <a href="{{ route('settings.index') }}"
                               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors text-xs font-semibold group"
                               title="ڕێکخستن و باکەپ">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-slate-600 group-hover:text-white transition-colors">
                                    @include('partials.icon', ['name' => 'settings', 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" class="truncate text-right">ڕێکخستن و باکەپ</span>
                            </a>
                        @endif
                    </nav>
                </div>

            </div>

            {{-- بەشی خوارەوە: بەکارهێنەر --}}
            <div class="border-t border-slate-100 p-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="footbar-avatar shrink-0">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                    <div x-show="sidebarOpen" class="min-w-0 flex-1">
                        <div class="truncate text-xs font-bold text-slate-800">{{ auth()->user()->name }}</div>
                        <div class="truncate text-[11px] text-slate-400">
                            {{ auth()->user()->isAdmin() ? 'بەڕێوەبەر' : 'بەرپرسی کۆگا' }}
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ── بەشی سەرەکی و ناوەڕۆک (Main Content Area) ── --}}
        <div class="flex flex-1 flex-col overflow-hidden min-w-0 bg-slate-50">

            {{-- هێڵی سەرەوە (Top Header Bar) --}}
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 md:px-6">
                {{-- دوگمەی تەنیشت و کاتژمێر --}}
                <div class="flex items-center gap-3">
                    <button type="button"
                            @click="if (window.innerWidth < 768) { mobileOpen = !mobileOpen } else { sidebarOpen = !sidebarOpen }"
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                            title="مێنیو">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <div class="hidden sm:block">
                        <div class="clock-time font-bold text-sm text-slate-800" dir="ltr" style="text-align: right" x-text="clock.time"></div>
                        <div class="clock-date text-[11px] text-slate-400" x-text="clock.date"></div>
                    </div>
                </div>

                {{-- لای چەپ: حاسیبە و دەرچوون --}}
                <div class="flex items-center gap-2">
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
    @livewireScripts
</body>
</html>
