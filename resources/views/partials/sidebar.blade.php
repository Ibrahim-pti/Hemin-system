@php
    // مێنیوەکە بەپێی مۆڵەتی بەکارهێنەر پیشان دەدرێت.
    // بەرپرسی کۆگا تەنها بەشی مەخزەن دەبینێت.
    $groups = [
        [
            'label' => null,
            'links' => [
                ['route' => 'dashboard', 'label' => 'داشبۆرد', 'icon' => 'dashboard', 'can' => null],
            ],
        ],
        [
            'label' => 'مەخزەن',
            'links' => [
                ['route' => 'items.index', 'label' => 'کاڵا و مەواد', 'icon' => 'items', 'can' => 'view_stock'],
                ['route' => 'stock.index', 'label' => 'جوڵەی مەخزەن', 'icon' => 'stock', 'can' => 'view_stock'],
                ['route' => 'counts.index', 'label' => 'جەردی کۆگا', 'icon' => 'counts', 'can' => 'manage_stock_counts'],
                ['route' => 'warehouses.index', 'label' => 'کۆگاکان', 'icon' => 'warehouses', 'can' => 'manage_items'],
            ],
        ],
        [
            'label' => 'کڕین',
            'links' => [
                ['route' => 'suppliers.index', 'label' => 'فرۆشیارەکان', 'icon' => 'suppliers', 'can' => 'manage_suppliers'],
                ['route' => 'purchases.index', 'label' => 'پسوولەی کڕین', 'icon' => 'purchases', 'can' => 'manage_purchases'],
            ],
        ],
        [
            'label' => 'فرۆشتن',
            'links' => [
                ['route' => 'customers.index', 'label' => 'کڕیارەکان', 'icon' => 'customers', 'can' => 'manage_customers'],
                ['route' => 'orders.index', 'label' => 'وەسڵ و داواکاری', 'icon' => 'orders', 'can' => 'manage_orders'],
            ],
        ],
        [
            'label' => 'پارە',
            'links' => [
                ['route' => 'payments.index', 'label' => 'حەقدی', 'icon' => 'payments', 'can' => 'manage_payments'],
                ['route' => 'cash.index', 'label' => 'قاسە', 'icon' => 'cash', 'can' => 'manage_cash'],
                ['route' => 'debts.index', 'label' => 'قەرزەکان', 'icon' => 'debts', 'can' => 'manage_payments'],
            ],
        ],
        [
            'label' => 'کار',
            'links' => [
                ['route' => 'employees.index', 'label' => 'کارمەندان', 'icon' => 'employees', 'can' => 'manage_employees'],
                ['route' => 'attendance.index', 'label' => 'هاتن و چوون', 'icon' => 'attendance', 'can' => 'manage_employees'],
                ['route' => 'external-jobs.index', 'label' => 'ئیشی خاریجی', 'icon' => 'external-jobs', 'can' => 'manage_external_jobs'],
            ],
        ],
        [
            'label' => 'سیستەم',
            'links' => [
                ['route' => 'reports.index', 'label' => 'راپۆرتەکان', 'icon' => 'reports', 'can' => 'view_reports'],
                ['route' => 'activity.index', 'label' => 'مێژووی کردارەکان', 'icon' => 'activity', 'can' => 'manage_settings'],
                ['route' => 'settings.index', 'label' => 'ڕێکخستن و باکەپ', 'icon' => 'settings', 'can' => 'manage_settings'],
            ],
        ],
    ];
@endphp

<div class="flex h-full flex-col bg-[--color-surface]">

    {{-- ناوی کارگە --}}
    <div class="border-b border-[--color-line] px-4 py-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[--color-brand-700] text-base font-bold text-white">
                هـ
            </div>
            <div class="min-w-0">
                <div class="truncate text-sm font-semibold leading-tight">
                    {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}
                </div>
                <div class="text-xs text-[--color-ink-soft]">سیستەمی بەڕێوەبردن</div>
            </div>
        </a>
    </div>

    {{-- بەستەرەکان --}}
    <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
        @foreach ($groups as $group)
            @php
                $visible = collect($group['links'])->filter(
                    fn ($link) => ! $link['can'] || auth()->user()->can($link['can'])
                );
            @endphp

            @if ($visible->isNotEmpty())
                <div>
                    @if ($group['label'])
                        <div class="mb-1.5 px-3 text-[0.7rem] font-semibold uppercase tracking-wide text-[--color-ink-soft]">
                            {{ $group['label'] }}
                        </div>
                    @endif

                    <div class="space-y-0.5">
                        @foreach ($visible as $link)
                            @php $active = request()->routeIs(str_replace('.index', '.*', $link['route'])); @endphp
                            <a href="{{ route($link['route']) }}"
                               class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition-colors
                                      {{ $active
                                          ? 'bg-[--color-brand-soft] font-semibold text-[--color-brand-800]'
                                          : 'text-[--color-ink] hover:bg-[--color-surface-soft]' }}">
                                <span class="{{ $active ? 'text-[--color-brand-700]' : 'text-[--color-ink-soft]' }}">
                                    @include('partials.icon', ['name' => $link['icon'], 'class' => 'size-[1.15rem]'])
                                </span>
                                <span class="truncate">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- بەکارهێنەر --}}
    <div class="border-t border-[--color-line] p-3">
        <div class="mb-2 flex items-center gap-2.5 px-1">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[--color-brand-soft] text-sm font-semibold text-[--color-brand-800]">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="min-w-0">
                <div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div>
                <div class="text-xs text-[--color-ink-soft]">
                    {{ auth()->user()->isAdmin() ? 'بەڕێوەبەر' : 'بەرپرسی کۆگا' }}
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost w-full">دەرچوون</button>
        </form>
    </div>
</div>
