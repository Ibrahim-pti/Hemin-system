{{-- ── مێنیوی سەرەکی سیستەم (Dark Navy Sidebar) ── --}}
{{-- NOTE: Using inline styles for critical layout to avoid Vite rebuild dependency --}}
<aside
    :style="{
        width: sidebarOpen ? '16rem' : '5rem',
        minWidth: sidebarOpen ? '16rem' : '5rem',
        transform: (window.innerWidth < 640 && !mobileOpen) ? 'translateX(100%)' : 'translateX(0)',
    }"
    style="position: sticky; top: 0; height: 100vh; background: #0f172a; border-left: 1px solid rgba(51,65,85,0.5); z-index: 50; flex-shrink: 0; transition: width 0.2s ease, min-width 0.2s ease, transform 0.2s ease; display: flex; flex-direction: column; user-select: none;"
    class="sidebar-nav"
    x-cloak>

    {{-- سەری مێنیو: لۆگۆ و ناوی کارگە --}}
    <div style="height: 4rem; display: flex; align-items: center; justify-content: space-between; padding: 0 1rem; border-bottom: 1px solid rgba(51,65,85,0.5); flex-shrink: 0;">
        <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 0.75rem; overflow: hidden; text-decoration: none;">
            <span style="display: flex; width: 2.25rem; height: 2.25rem; align-items: center; justify-content: center; border-radius: 0.75rem; background: #2563eb; color: white; font-weight: bold; font-size: 1rem; flex-shrink: 0;">
                هـ
            </span>
            <div x-show="sidebarOpen" x-transition.opacity style="min-width: 0;">
                <div style="font-size: 0.875rem; font-weight: 700; color: white; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; letter-spacing: -0.01em;">
                    {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}
                </div>
                <div style="font-size: 0.69rem; color: #94a3b8; font-weight: 500;">سیستەمی بەڕێوەبردن</div>
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
    <div style="flex: 1; overflow-y: auto; padding: 1rem 0.75rem; display: flex; flex-direction: column; gap: 1.25rem;">

        {{-- سەرەکی / داشبۆرد --}}
        <div>
            <a href="{{ route('dashboard') }}"
               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ request()->routeIs('dashboard') ? 'background: #2563eb; color: white;' : 'color: #cbd5e1;' }}"
               onmouseover="if(!this.classList.contains('active-link'))this.style.background='rgba(30,41,59,0.7)';this.style.color='white';"
               onmouseout="if(!this.classList.contains('active-link')){this.style.background='';this.style.color='#cbd5e1';}"
               class="{{ request()->routeIs('dashboard') ? 'active-link' : '' }}"
               title="داشبۆرد">
                <span style="display: flex; width: 2rem; height: 2rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 0.5rem; {{ request()->routeIs('dashboard') ? 'background: rgba(255,255,255,0.2); color: white;' : 'background: rgba(30,41,59,1); color: #60a5fa;' }}">
                    @include('partials.icon', ['name' => 'dashboard', 'class' => 'size-4.5'])
                </span>
                <span x-show="sidebarOpen" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">داشبۆردی سەرەکی</span>
            </a>
        </div>

        @php
            $sections = [
                [
                    'title' => 'کۆگا و لایەنەکان',
                    'color' => '#3b82f6',
                    'colorLight' => 'rgba(59,130,246,0.1)',
                    'colorText' => '#60a5fa',
                    'items' => [
                        ['route' => 'items.*', 'href' => route('items.index'), 'label' => 'دۆخی کۆگا', 'icon' => 'items', 'can' => 'view_stock'],
                        ['route' => 'counts.*', 'href' => route('counts.index'), 'label' => 'جەردی کۆگا', 'icon' => 'counts', 'can' => 'manage_stock_counts'],
                        ['route' => 'warehouses.*', 'href' => route('warehouses.index'), 'label' => 'کۆگاکان', 'icon' => 'warehouses', 'can' => 'manage_items'],
                        ['route' => 'customers.*', 'href' => route('customers.index'), 'label' => 'کڕیارەکان', 'icon' => 'customers', 'can' => 'manage_customers'],
                        ['route' => 'suppliers.*', 'href' => route('suppliers.index'), 'label' => 'فرۆشیارەکان', 'icon' => 'suppliers', 'can' => 'manage_suppliers'],
                        ['route' => 'employees.*', 'href' => route('employees.index'), 'label' => 'کارمەندان', 'icon' => 'employees', 'can' => 'manage_employees'],
                    ],
                ],
                [
                    'title' => 'فرۆشتن و دارایی',
                    'color' => '#0891b2',
                    'colorLight' => 'rgba(8,145,178,0.1)',
                    'colorText' => '#22d3ee',
                    'items' => [
                        ['route' => 'orders.*', 'href' => route('orders.index'), 'label' => 'وەسڵ و داواکاری', 'icon' => 'orders', 'can' => 'manage_orders'],
                        ['route' => 'purchases.*', 'href' => route('purchases.index'), 'label' => 'پسوولەی کڕین', 'icon' => 'purchases', 'can' => 'manage_purchases'],
                        ['route' => 'external-jobs.*', 'href' => route('external-jobs.index'), 'label' => 'ئیشی خاریجی', 'icon' => 'external-jobs', 'can' => 'manage_external_jobs'],
                        ['route' => 'cash.*', 'href' => route('cash.index'), 'label' => 'قاسە', 'icon' => 'cash', 'can' => 'manage_cash'],
                        ['route' => 'payments.*', 'href' => route('payments.index'), 'label' => 'حەقدی و پارەدان', 'icon' => 'payments', 'can' => 'manage_payments'],
                        ['route' => 'debts.*', 'href' => route('debts.index'), 'label' => 'قەرزەکان', 'icon' => 'debts', 'can' => 'manage_payments'],
                    ],
                ],
                [
                    'title' => 'ڕاپۆرت و سیستەم',
                    'color' => '#7c3aed',
                    'colorLight' => 'rgba(124,58,237,0.1)',
                    'colorText' => '#a78bfa',
                    'items' => [
                        ['route' => 'attendance.index', 'href' => route('attendance.index'), 'label' => 'هاتن و چوون', 'icon' => 'attendance', 'can' => 'manage_employees'],
                        ['route' => 'attendance.wages', 'href' => route('attendance.wages'), 'label' => 'حەقدەستەکان', 'icon' => 'employees', 'can' => 'manage_employees'],
                        ['route' => 'reports.*', 'href' => route('reports.index'), 'label' => 'ڕاپۆرتەکان', 'icon' => 'reports', 'can' => 'view_reports'],
                        ['route' => 'activity.*', 'href' => route('activity.index'), 'label' => 'مێژووی کردارەکان', 'icon' => 'activity', 'can' => 'manage_settings'],
                        ['route' => 'settings.*', 'href' => route('settings.index'), 'label' => 'ڕێکخستن و باکەپ', 'icon' => 'settings', 'can' => 'manage_settings'],
                    ],
                ],
            ];
        @endphp

        @foreach ($sections as $section)
            <div>
                <div x-show="sidebarOpen" style="padding: 0 0.625rem; margin-bottom: 0.375rem; font-size: 0.69rem; font-weight: 700; color: #64748b; letter-spacing: 0.05em;">
                    {{ $section['title'] }}
                </div>
                <nav style="display: flex; flex-direction: column; gap: 0.125rem;">
                    @foreach ($section['items'] as $item)
                        @if (auth()->user()->can($item['can']))
                            @php
                                $isActive = request()->routeIs($item['route']);
                            @endphp
                            <a href="{{ $item['href'] }}"
                               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.625rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ $isActive ? 'background: ' . $section['color'] . '; color: white;' : 'color: #cbd5e1;' }}"
                               onmouseover="if(!this.classList.contains('active-link'))this.style.background='rgba(30,41,59,0.7)';this.style.color='white';"
                               onmouseout="if(!this.classList.contains('active-link')){this.style.background='';this.style.color='#cbd5e1';}"
                               class="{{ $isActive ? 'active-link' : '' }}"
                               title="{{ $item['label'] }}">
                                <span style="display: flex; width: 2rem; height: 2rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 0.5rem; {{ $isActive ? 'background: rgba(255,255,255,0.2); color: white;' : 'background: ' . $section['colorLight'] . '; color: ' . $section['colorText'] . ';' }}">
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

    {{-- بەشی خوارەوە: بەکارهێنەر --}}
    <div style="border-top: 1px solid rgba(51,65,85,0.5); padding: 0.75rem; flex-shrink: 0; background: rgba(2,6,23,0.4);">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="display: flex; width: 2rem; height: 2rem; align-items: center; justify-content: center; border-radius: 50%; background: rgba(37,99,235,0.3); color: #60a5fa; font-weight: bold; font-size: 0.75rem; flex-shrink: 0; border: 1px solid rgba(59,130,246,0.3);">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <div x-show="sidebarOpen" x-transition.opacity style="min-width: 0; flex: 1;">
                <div style="font-size: 0.75rem; font-weight: 700; color: white; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ auth()->user()->name }}</div>
                <div style="font-size: 0.69rem; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ auth()->user()->isAdmin() ? 'بەڕێوەبەر' : 'بەرپرسی کۆگا' }}
                </div>
            </div>
        </div>
    </div>
</aside>
