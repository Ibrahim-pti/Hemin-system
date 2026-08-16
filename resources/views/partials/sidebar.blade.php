{{-- ── مێنیوی سەرەکی سیستەم (Sidebar Navigation Partial) ── --}}
<aside :class="{
           'translate-x-0': mobileOpen,
           'translate-x-full md:translate-x-0': !mobileOpen,
           'w-64 min-w-[16rem]': sidebarOpen,
           'w-20 min-w-[5rem]': !sidebarOpen
       }"
       class="fixed inset-y-0 right-0 z-50 flex h-full flex-col border-l border-slate-200 bg-white shadow-sm transition-all duration-200 md:static md:z-auto shrink-0 select-none">

    {{-- سەری مێنیو: لۆگۆ و ناوی کارگە --}}
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-100 px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden group">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white font-bold shadow-xs group-hover:scale-105 transition-transform">
                هـ
            </span>
            <div x-show="sidebarOpen" class="min-w-0 transition-opacity">
                <div class="truncate text-sm font-bold text-slate-800">
                    {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}
                </div>
                <div class="truncate text-[11px] text-slate-400 font-medium">سیستەمی بەڕێوەبردن</div>
            </div>
        </a>

        {{-- داخستنی مۆبایل --}}
        <button @click="mobileOpen = false" class="text-slate-400 hover:text-slate-600 md:hidden p-1">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- بەشی بەستەرەکانی مێنیو --}}
    <div class="flex-1 overflow-y-auto p-3 space-y-4">

        {{-- سەرەکی --}}
        <div>
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-blue-700' }}"
               title="داشبۆرد">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-600' }}">
                    @include('partials.icon', ['name' => 'dashboard', 'class' => 'size-4.5'])
                </span>
                <span x-show="sidebarOpen" class="truncate">سەرەکی / داشبۆرد</span>
            </a>
        </div>

        {{-- ١. کۆگا و لایەنەکان --}}
        <div>
            <div x-show="sidebarOpen" class="px-2.5 mb-1.5 text-[11px] font-bold text-slate-400">
                کۆگا و لایەنەکان
            </div>
            <nav class="space-y-1">
                @if (auth()->user()->can('view_stock'))
                    <a href="{{ route('items.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('items.*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}"
                       title="دۆخی کۆگا">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('items.*') ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-600' }}">
                            @include('partials.icon', ['name' => 'items', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">دۆخی کۆگا</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_stock_counts'))
                    <a href="{{ route('counts.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('counts.*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}"
                       title="جەردی کۆگا">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('counts.*') ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-600' }}">
                            @include('partials.icon', ['name' => 'counts', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">جەردی کۆگا</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_items'))
                    <a href="{{ route('warehouses.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('warehouses.*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}"
                       title="کۆگاکان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('warehouses.*') ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-600' }}">
                            @include('partials.icon', ['name' => 'warehouses', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">کۆگاکان</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_customers'))
                    <a href="{{ route('customers.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}"
                       title="کڕیارەکان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('customers.*') ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-600' }}">
                            @include('partials.icon', ['name' => 'customers', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">کڕیارەکان</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_suppliers'))
                    <a href="{{ route('suppliers.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('suppliers.*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}"
                       title="فرۆشیارەکان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('suppliers.*') ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-600' }}">
                            @include('partials.icon', ['name' => 'suppliers', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">فرۆشیارەکان</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_employees'))
                    <a href="{{ route('employees.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('employees.*') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}"
                       title="کارمەندان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('employees.*') ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-600' }}">
                            @include('partials.icon', ['name' => 'employees', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">کارمەندان</span>
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
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('orders.*') ? 'bg-cyan-50 text-cyan-800 font-bold' : 'text-slate-700 hover:bg-cyan-50 hover:text-cyan-800' }}"
                       title="وەسڵ و داواکاری">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('orders.*') ? 'bg-cyan-600 text-white shadow-xs' : 'bg-cyan-50 text-cyan-700' }}">
                            @include('partials.icon', ['name' => 'orders', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">وەسڵ و داواکاری</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_purchases'))
                    <a href="{{ route('purchases.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('purchases.*') ? 'bg-cyan-50 text-cyan-800 font-bold' : 'text-slate-700 hover:bg-cyan-50 hover:text-cyan-800' }}"
                       title="پسوولەی کڕین">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('purchases.*') ? 'bg-cyan-600 text-white shadow-xs' : 'bg-cyan-50 text-cyan-700' }}">
                            @include('partials.icon', ['name' => 'purchases', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">پسوولەی کڕین</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_external_jobs'))
                    <a href="{{ route('external-jobs.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('external-jobs.*') ? 'bg-amber-50 text-amber-800 font-bold' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-800' }}"
                       title="ئیشی خاریجی">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('external-jobs.*') ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-600' }}">
                            @include('partials.icon', ['name' => 'external-jobs', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">ئیشی خاریجی</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_cash'))
                    <a href="{{ route('cash.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('cash.*') ? 'bg-rose-50 text-rose-800 font-bold' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-800' }}"
                       title="قاسە">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('cash.*') ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-600' }}">
                            @include('partials.icon', ['name' => 'cash', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">قاسە</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_payments'))
                    <a href="{{ route('payments.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('payments.*') ? 'bg-rose-50 text-rose-800 font-bold' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-800' }}"
                       title="حەقدی و پارەدان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('payments.*') ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-600' }}">
                            @include('partials.icon', ['name' => 'payments', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">حەقدی و پارەدان</span>
                    </a>

                    <a href="{{ route('debts.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('debts.*') ? 'bg-rose-50 text-rose-800 font-bold' : 'text-slate-700 hover:bg-rose-50 hover:text-rose-800' }}"
                       title="قەرزەکان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('debts.*') ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-600' }}">
                            @include('partials.icon', ['name' => 'debts', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">قەرزەکان</span>
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
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('attendance.index') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}"
                       title="هاتن و چوون">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('attendance.index') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-600' }}">
                            @include('partials.icon', ['name' => 'attendance', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">هاتن و چوون</span>
                    </a>

                    <a href="{{ route('attendance.wages') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('attendance.wages') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800' }}"
                       title="حەقدەستەکان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('attendance.wages') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-600' }}">
                            @include('partials.icon', ['name' => 'employees', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">حەقدەستەکان</span>
                    </a>
                @endif

                @if (auth()->user()->can('view_reports'))
                    <a href="{{ route('reports.show', 'profit') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('*reports/profit*') ? 'bg-purple-50 text-purple-800 font-bold' : 'text-slate-700 hover:bg-purple-50 hover:text-purple-800' }}"
                       title="راپۆرتی قازانج">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->is('*reports/profit*') ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-600' }}">
                            @include('partials.icon', ['name' => 'reports', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">راپۆرتی قازانج</span>
                    </a>

                    <a href="{{ route('reports.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('reports.index') ? 'bg-purple-50 text-purple-800 font-bold' : 'text-slate-700 hover:bg-purple-50 hover:text-purple-800' }}"
                       title="هەموو راپۆرت">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('reports.index') ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-600' }}">
                            @include('partials.icon', ['name' => 'reports', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">هەموو راپۆرت</span>
                    </a>
                @endif

                @if (auth()->user()->can('manage_settings'))
                    <a href="{{ route('activity.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('activity.*') ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}"
                       title="مێژووی کردارەکان">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('activity.*') ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                            @include('partials.icon', ['name' => 'activity', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">مێژووی کردارەکان</span>
                    </a>

                    <a href="{{ route('settings.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('settings.*') ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}"
                       title="ڕێکخستن و باکەپ">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('settings.*') ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                            @include('partials.icon', ['name' => 'settings', 'class' => 'size-4.5'])
                        </span>
                        <span x-show="sidebarOpen" class="truncate">ڕێکخستن و باکەپ</span>
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
