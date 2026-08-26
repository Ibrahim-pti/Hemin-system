{{-- ── مێنیوی سەرەکی سیستەم (Dark Navy Sidebar) ── --}}
{{-- NOTE: Using inline styles for critical layout to avoid Vite rebuild dependency --}}
<aside
    id="main-sidebar"
    :style="{
        width: sidebarOpen ? '16rem' : '5rem',
        minWidth: sidebarOpen ? '16rem' : '5rem',
        transform: (window.innerWidth < 640 && !mobileOpen) ? 'translateX(100%)' : 'translateX(0)',
    }"
    style="position: sticky; top: 0; height: 100vh; width: 16rem; min-width: 16rem; background: #0f172a; border-left: 1px solid rgba(255,255,255,0.07); z-index: 50; flex-shrink: 0; transition: width 0.2s ease, min-width 0.2s ease, transform 0.2s ease; display: flex; flex-direction: column; user-select: none;"
    class="sidebar-nav sidebar-no-transition"
    x-init="$nextTick(() => { $el.classList.remove('sidebar-no-transition'); })">

    {{-- سەری مێنیو: لۆگۆ و ناوی کارگە --}}
    <div style="height: 4rem; display: flex; align-items: center; justify-content: space-between; padding: 0 1rem; border-bottom: 1px solid rgba(255,255,255,0.07); flex-shrink: 0;">
        <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 0.75rem; overflow: hidden; text-decoration: none;">
            <span style="display: flex; width: 2.25rem; height: 2.25rem; align-items: center; justify-content: center; border-radius: 0.75rem; background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.25); font-weight: bold; font-size: 1rem; flex-shrink: 0;">
                هـ
            </span>
            <div x-show="sidebarOpen" style="min-width: 0;">
                <div style="font-size: 0.85rem; font-weight: 700; color: #f1f5f9; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; letter-spacing: -0.01em;">
                    {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}
                </div>
                <div style="font-size: 0.68rem; color: #64748b; font-weight: 500;">سیستەمی بەڕێوەبردن</div>
            </div>
        </a>

        {{-- داخستنی مۆبایل --}}
        <button @click="mobileOpen = false" style="color: #94a3b8; padding: 0.25rem; display: none;" class="sm-hidden-toggle">
            <svg style="width: 1.25rem; height: 1.25rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- بەشی بەستەرەکانی مێنیو --}}
    <div class="sidebar-scroll" style="flex: 1; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none; padding: 1rem 0.75rem; display: flex; flex-direction: column; gap: 1rem;">

        {{-- سەرەکی / داشبۆرد --}}
        <div>
            @php $isDashboard = request()->routeIs('dashboard'); @endphp
            <a href="{{ route('dashboard') }}"
               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.625rem; border-radius: 0.75rem; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: background-color 0.15s ease, color 0.15s ease; {{ $isDashboard ? 'background: rgba(59,130,246,0.12); color: #60a5fa; border: 1px solid rgba(59,130,246,0.22);' : 'color: #94a3b8; border: 1px solid transparent;' }}"
               class="sidebar-link {{ $isDashboard ? 'active-link' : '' }}">
                <span style="display: flex; width: 2rem; height: 2rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 0.5rem; {{ $isDashboard ? 'background: rgba(59,130,246,0.2); color: #93c5fd;' : 'background: rgba(255,255,255,0.04); color: #94a3b8; border: 1px solid rgba(255,255,255,0.04);' }}">
                    @include('partials.icon', ['name' => 'dashboard', 'class' => 'size-4.5'])
                </span>
                <span x-show="sidebarOpen" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">داشبۆردی سەرەکی</span>
            </a>
        </div>

        @php
            $sections = [
                [
                    'title' => 'کۆگا و لایەنەکان',
                    'activeBg' => 'rgba(14, 165, 233, 0.12)',
                    'activeBorder' => 'rgba(14, 165, 233, 0.22)',
                    'activeText' => '#38bdf8',
                    'activeIconBg' => 'rgba(14, 165, 233, 0.2)',
                    'activeIconColor' => '#7dd3fc',
                    'items' => [
                        ['route' => 'items.*', 'href' => route('items.index'), 'label' => 'دۆخی کۆگا', 'icon' => 'items', 'can' => 'view_stock'],
                        ['route' => 'counts.*', 'href' => route('counts.index'), 'label' => 'جەردی کۆگا', 'icon' => 'counts', 'can' => 'manage_stock_counts'],
                        ['route' => 'warehouses.*', 'href' => route('warehouses.index'), 'label' => 'کۆگاکان', 'icon' => 'warehouses', 'can' => 'manage_items'],
                        ['route' => 'suppliers.*', 'href' => route('suppliers.index'), 'label' => 'فرۆشیارەکان', 'icon' => 'suppliers', 'can' => 'manage_suppliers'],
                        ['route' => 'employees.*', 'href' => route('employees.index'), 'label' => 'کارمەندان', 'icon' => 'employees', 'can' => 'manage_employees'],
                    ],
                ],
                [
                    'title' => 'فرۆشتن و دارایی',
                    'activeBg' => 'rgba(20, 184, 166, 0.12)',
                    'activeBorder' => 'rgba(20, 184, 166, 0.22)',
                    'activeText' => '#2dd4bf',
                    'activeIconBg' => 'rgba(20, 184, 166, 0.2)',
                    'activeIconColor' => '#5eead4',
                    'items' => [
                        ['route' => 'orders.*', 'href' => route('orders.index'), 'label' => 'فرۆشتن', 'icon' => 'orders', 'can' => 'manage_orders'],
                        ['route' => 'customers.*', 'href' => route('customers.index'), 'label' => 'کڕیاران', 'icon' => 'customers', 'can' => 'manage_customers'],
                        ['route' => 'purchases.*', 'href' => route('purchases.index'), 'label' => 'پسوولەی کڕین', 'icon' => 'purchases', 'can' => 'manage_purchases'],
                        ['route' => 'external-jobs.*', 'href' => route('external-jobs.index'), 'label' => 'ئیشی خاریجی', 'icon' => 'external-jobs', 'can' => 'manage_external_jobs'],
                        ['route' => 'cash.*', 'href' => route('cash.index'), 'label' => 'قاسە', 'icon' => 'cash', 'can' => 'manage_cash'],
                        ['route' => 'payments.*', 'href' => route('payments.index'), 'label' => 'حەقدی و پارەدان', 'icon' => 'payments', 'can' => 'manage_payments'],
                        ['route' => 'debts.*', 'href' => route('debts.index'), 'label' => 'قەرزەکان', 'icon' => 'debts', 'can' => 'manage_payments'],
                        ['route' => 'customers.statement|statement.*', 'href' => route('statement.index'), 'label' => 'کەشف حیسابی', 'icon' => 'reports', 'can' => 'manage_customers'],
                    ],
                ],
                [
                    'title' => 'ڕاپۆرت و سیستەم',
                    'activeBg' => 'rgba(139, 92, 246, 0.12)',
                    'activeBorder' => 'rgba(139, 92, 246, 0.22)',
                    'activeText' => '#a78bfa',
                    'activeIconBg' => 'rgba(139, 92, 246, 0.2)',
                    'activeIconColor' => '#c4b5fd',
                    'items' => [
                        ['route' => 'reports.*', 'href' => route('reports.index'), 'label' => 'ڕاپۆرتەکان', 'icon' => 'reports', 'can' => 'view_reports'],
                        ['route' => 'settings.*', 'href' => route('settings.index'), 'label' => 'ڕێکخستن و باکەپ', 'icon' => 'settings', 'can' => 'manage_settings'],
                    ],
                ],
            ];
        @endphp

        @foreach ($sections as $section)
            <div>
                <div x-show="sidebarOpen" style="padding: 0 0.625rem; margin-bottom: 0.375rem; font-size: 0.68rem; font-weight: 600; color: #64748b; letter-spacing: 0.04em;">
                    {{ $section['title'] }}
                </div>
                <nav style="display: flex; flex-direction: column; gap: 0.125rem;">
                    @foreach ($section['items'] as $item)
                        @if (auth()->user()->can($item['can']))
                            @php
                                $isActive = request()->routeIs(...explode('|', $item['route']));
                            @endphp
                            <a href="{{ $item['href'] }}"
                               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.625rem; border-radius: 0.75rem; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: background-color 0.15s ease, color 0.15s ease; {{ $isActive ? 'background: ' . $section['activeBg'] . '; color: ' . $section['activeText'] . '; border: 1px solid ' . $section['activeBorder'] . ';' : 'color: #94a3b8; border: 1px solid transparent;' }}"
                               class="sidebar-link {{ $isActive ? 'active-link' : '' }}">
                                <span style="display: flex; width: 2rem; height: 2rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 0.5rem; {{ $isActive ? 'background: ' . $section['activeIconBg'] . '; color: ' . $section['activeIconColor'] . ';' : 'background: rgba(255,255,255,0.04); color: #94a3b8; border: 1px solid rgba(255,255,255,0.04);' }}">
                                    @include('partials.icon', ['name' => $item['icon'], 'class' => 'size-4.5'])
                                </span>
                                <span x-show="sidebarOpen" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        @endforeach

    </div>
</aside>
