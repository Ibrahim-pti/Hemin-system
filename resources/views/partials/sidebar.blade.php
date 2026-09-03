{{-- ── مێنیوی سەرەکی سیستەم (Dark Navy Sidebar) ── --}}
{{-- NOTE: Using inline styles for critical layout to avoid Vite rebuild dependency --}}
<aside
    id="main-sidebar"
    :class="{ 'mobile-open': mobileOpen }"
    :style="{
        width: (window.innerWidth >= 640) ? (sidebarOpen ? '13.5rem' : '4.5rem') : '',
        minWidth: (window.innerWidth >= 640) ? (sidebarOpen ? '13.5rem' : '4.5rem') : '',
    }"
    style="background: #0f172a; border-left: 1px solid rgba(255,255,255,0.07); flex-shrink: 0; display: flex; flex-direction: column; user-select: none;"
    class="no-print sidebar-nav sidebar-no-transition"
    x-init="$nextTick(() => { $el.classList.remove('sidebar-no-transition'); })">

    {{-- سەری مێنیو: لۆگۆ و ناوی کارگە --}}
    <div style="height: 3.75rem; display: flex; align-items: center; justify-content: space-between; padding: 0 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.07); flex-shrink: 0;">
        <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 0.55rem; overflow: hidden; text-decoration: none;">
            <span style="display: flex; width: 2rem; height: 2rem; align-items: center; justify-content: center; border-radius: 0.5rem; background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.25); font-weight: 800; font-size: 0.9rem; flex-shrink: 0;">
                هـ
            </span>
            <div x-show="sidebarOpen || window.innerWidth < 640" style="min-width: 0;">
                <div style="font-size: 0.79rem; font-weight: 700; color: #f1f5f9; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; letter-spacing: -0.01em;">
                    {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}
                </div>
                <div style="font-size: 0.62rem; color: #64748b; font-weight: 500;">سیستەمی بەڕێوەبردن</div>
            </div>
        </a>

        {{-- داخستنی مۆبایل --}}
        <button type="button" @click="mobileOpen = false"
                class="flex sm:hidden size-8 shrink-0 items-center justify-center rounded-lg border border-slate-700/80 bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors cursor-pointer active:scale-95"
                title="داخستن">
            <svg style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- بەشی بەستەرەکانی مێنیو (Sleek and Slim) --}}
    <div class="sidebar-scroll" style="flex: 1; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none; padding: 0.4rem 0.45rem 1rem 0.45rem; display: flex; flex-direction: column; gap: 0.4rem;">

        {{-- سەرەکی / داشبۆرد --}}
        <div>
            @php
                $isWasta = auth()->user()->isStorekeeper() && !auth()->user()->isAdmin();
                $dashRoute = $isWasta ? 'workshop.index' : 'dashboard';
                $dashLabel = 'داشبۆردی سەرەکی';
                $isDashboard = request()->routeIs('dashboard') || ($isWasta && request()->routeIs('workshop.index'));
            @endphp
            <a href="{{ route($dashRoute) }}"
               @click="if (window.innerWidth < 640) mobileOpen = false"
               style="display: flex; align-items: center; gap: 0.55rem; padding: 0.32rem 0.45rem; border-radius: 0.55rem; font-size: 0.74rem; font-weight: 600; text-decoration: none; transition: background-color 0.15s ease, color 0.15s ease; {{ $isDashboard ? 'background: rgba(59,130,246,0.14); color: #60a5fa; border: 1px solid rgba(59,130,246,0.25);' : 'color: #94a3b8; border: 1px solid transparent;' }}"
               class="sidebar-link {{ $isDashboard ? 'active-link' : '' }}">
                <span style="display: flex; width: 1.75rem; height: 1.75rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 0.45rem; {{ $isDashboard ? 'background: rgba(59,130,246,0.22); color: #93c5fd;' : 'background: rgba(255,255,255,0.04); color: #94a3b8; border: 1px solid rgba(255,255,255,0.04);' }}">
                    @include('partials.icon', ['name' => 'dashboard', 'class' => 'size-3.5'])
                </span>
                <span x-show="sidebarOpen || mobileOpen || window.innerWidth < 640" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $dashLabel }}</span>
            </a>
        </div>

        @php
            $sections = [
                [
                    'title' => 'کارگە و دروستکردن',
                    'activeBg' => 'rgba(99, 102, 241, 0.14)',
                    'activeBorder' => 'rgba(99, 102, 241, 0.25)',
                    'activeText' => '#818cf8',
                    'activeIconBg' => 'rgba(99, 102, 241, 0.22)',
                    'activeIconColor' => '#a5b4fc',
                    'items' => array_values(array_filter([
                        $isWasta ? ['route' => 'workshop.orders', 'href' => route('workshop.orders'), 'label' => 'داواکارییەکانی کارگە', 'icon' => 'orders', 'can' => 'view_workshop'] : null,
                        $isWasta ? ['route' => 'workshop.materials', 'href' => route('workshop.materials'), 'label' => 'مەخزەن', 'icon' => 'items', 'can' => 'view_workshop'] : null,
                        ['route' => 'workshop.employees', 'href' => route('workshop.employees'), 'label' => 'کارمەندان', 'icon' => 'employees', 'can' => 'view_workshop'],
                    ])),
                ],
                [
                    'title' => 'کۆگا و جەرد',
                    'activeBg' => 'rgba(59, 130, 246, 0.14)',
                    'activeBorder' => 'rgba(59, 130, 246, 0.25)',
                    'activeText' => '#60a5fa',
                    'activeIconBg' => 'rgba(59, 130, 246, 0.22)',
                    'activeIconColor' => '#93c5fd',
                    'items' => [
                        ['route' => 'counts.*', 'href' => route('counts.index'), 'label' => 'جەردی کۆگا', 'icon' => 'counts', 'can' => 'manage_settings'],
                        ['route' => 'warehouses.*', 'href' => route('warehouses.index'), 'label' => 'کۆگا', 'icon' => 'warehouses', 'can' => 'manage_settings'],
                    ],
                ],
                [
                    'title' => 'فرۆشتن و دارایی',
                    'activeBg' => 'rgba(20, 184, 166, 0.14)',
                    'activeBorder' => 'rgba(20, 184, 166, 0.25)',
                    'activeText' => '#2dd4bf',
                    'activeIconBg' => 'rgba(20, 184, 166, 0.22)',
                    'activeIconColor' => '#5eead4',
                    'items' => [
                        ['route' => 'orders.*', 'href' => route('orders.index'), 'label' => 'فرۆشتن', 'icon' => 'orders', 'can' => 'manage_orders'],
                        ['route' => 'customers.*', 'href' => route('customers.index'), 'label' => 'کڕیاران', 'icon' => 'customers', 'can' => 'manage_customers'],
                        ['route' => 'purchases.*', 'href' => route('purchases.index'), 'label' => 'کڕینەکان', 'icon' => 'purchases', 'can' => 'manage_purchases'],
                        ['route' => 'cash.*', 'href' => route('cash.index'), 'label' => 'قاسە', 'icon' => 'cash', 'can' => 'manage_cash'],
                        ['route' => 'payments.*', 'href' => route('payments.index'), 'label' => 'حەقدی موشتەری', 'icon' => 'payments', 'can' => 'manage_payments'],
                        ['route' => 'debts.*', 'href' => route('debts.index'), 'label' => 'قەرزەکان', 'icon' => 'debts', 'can' => 'manage_payments'],
                        ['route' => 'customers.statement|statement.*', 'href' => route('statement.index'), 'label' => 'کەشف حیسابی', 'icon' => 'reports', 'can' => 'manage_customers'],
                    ],
                ],
                [
                    'title' => 'ڕاپۆرت و سیستەم',
                    'activeBg' => 'rgba(139, 92, 246, 0.14)',
                    'activeBorder' => 'rgba(139, 92, 246, 0.25)',
                    'activeText' => '#a78bfa',
                    'activeIconBg' => 'rgba(139, 92, 246, 0.22)',
                    'activeIconColor' => '#c4b5fd',
                    'items' => [
                        ['route' => 'reports.*', 'href' => route('reports.index'), 'label' => 'ڕاپۆرتەکان', 'icon' => 'reports', 'can' => 'view_reports'],
                        ['route' => 'settings.*', 'href' => route('settings.index'), 'label' => 'ڕێکخستن و باکەپ', 'icon' => 'settings', 'can' => 'manage_settings'],
                    ],
                ],
            ];
        @endphp

        @foreach ($sections as $section)
            @php
                $visibleItems = array_filter($section['items'], fn ($it) => auth()->user()->can($it['can']));
            @endphp
            @if (count($visibleItems) > 0)
                <div>
                    <div x-show="sidebarOpen || mobileOpen || window.innerWidth < 640" style="padding: 0.15rem 0.5rem; margin-bottom: 0.15rem; font-size: 0.65rem; font-weight: 700; color: #64748b; letter-spacing: 0.03em;">
                        {{ $section['title'] }}
                    </div>
                    <nav style="display: flex; flex-direction: column; gap: 0.125rem;">
                        @foreach ($visibleItems as $item)
                            @php
                                $isActive = isset($item['activeCheck'])
                                    ? $item['activeCheck']()
                                    : request()->routeIs(...explode('|', $item['route']));
                            @endphp
                            <a href="{{ $item['href'] }}"
                               @click="if (window.innerWidth < 640) mobileOpen = false"
                               style="display: flex; align-items: center; gap: 0.55rem; padding: 0.32rem 0.45rem; border-radius: 0.55rem; font-size: 0.74rem; font-weight: 600; text-decoration: none; transition: background-color 0.15s ease, color 0.15s ease; {{ $isActive ? 'background: ' . $section['activeBg'] . '; color: ' . $section['activeText'] . '; border: 1px solid ' . $section['activeBorder'] . ';' : 'color: #94a3b8; border: 1px solid transparent;' }}"
                               class="sidebar-link {{ $isActive ? 'active-link' : '' }}">
                                <span style="display: flex; width: 1.75rem; height: 1.75rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 0.45rem; {{ $isActive ? 'background: ' . $section['activeIconBg'] . '; color: ' . $section['activeIconColor'] . ';' : 'background: rgba(255,255,255,0.04); color: #94a3b8; border: 1px solid rgba(255,255,255,0.04);' }}">
                                    @include('partials.icon', ['name' => $item['icon'], 'class' => 'size-3.5'])
                                </span>
                                <span x-show="sidebarOpen || mobileOpen || window.innerWidth < 640" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endif
        @endforeach

    </div>
</aside>
