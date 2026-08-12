@php
    // مێنیوەکە بەپێی مۆڵەتی بەکارهێنەر پیشان دەدرێت.
    // بەرپرسی کۆگا تەنها بەشی مەخزەن دەبینێت.
    $groups = [
        [
            'label' => null,
            'links' => [
                ['route' => 'dashboard', 'label' => 'داشبۆرد', 'can' => null],
            ],
        ],
        [
            'label' => 'مەخزەن',
            'links' => [
                ['route' => 'items.index', 'label' => 'کاڵا و مەواد', 'can' => 'view_stock'],
                ['route' => 'stock.index', 'label' => 'جوڵەی مەخزەن', 'can' => 'view_stock'],
                ['route' => 'counts.index', 'label' => 'جەردی کۆگا', 'can' => 'manage_stock_counts'],
                ['route' => 'warehouses.index', 'label' => 'کۆگاکان', 'can' => 'manage_items'],
            ],
        ],
        [
            'label' => 'کڕین',
            'links' => [
                ['route' => 'suppliers.index', 'label' => 'فرۆشیارەکان', 'can' => 'manage_suppliers'],
                ['route' => 'purchases.index', 'label' => 'پسوولەی کڕین', 'can' => 'manage_purchases'],
            ],
        ],
        [
            'label' => 'فرۆشتن',
            'links' => [
                ['route' => 'customers.index', 'label' => 'کڕیارەکان', 'can' => 'manage_customers'],
                ['route' => 'orders.index', 'label' => 'وەسڵ و داواکاری', 'can' => 'manage_orders'],
            ],
        ],
        [
            'label' => 'پارە',
            'links' => [
                ['route' => 'payments.index', 'label' => 'حەقدی', 'can' => 'manage_payments'],
                ['route' => 'cash.index', 'label' => 'قاسە', 'can' => 'manage_cash'],
                ['route' => 'debts.index', 'label' => 'قەرزەکان', 'can' => 'manage_payments'],
            ],
        ],
        [
            'label' => 'کار',
            'links' => [
                ['route' => 'employees.index', 'label' => 'کارمەندان', 'can' => 'manage_employees'],
                ['route' => 'attendance.index', 'label' => 'هاتن و چوون', 'can' => 'manage_employees'],
                ['route' => 'external-jobs.index', 'label' => 'ئیشی خاریجی', 'can' => 'manage_external_jobs'],
            ],
        ],
        [
            'label' => 'سیستەم',
            'links' => [
                ['route' => 'reports.index', 'label' => 'راپۆرتەکان', 'can' => 'view_reports'],
                ['route' => 'activity.index', 'label' => 'مێژووی کردارەکان', 'can' => 'manage_settings'],
                ['route' => 'settings.index', 'label' => 'ڕێکخستن و باکەپ', 'can' => 'manage_settings'],
            ],
        ],
    ];
@endphp

<div class="flex h-full flex-col">

    {{-- ناوی کارگە --}}
    <div class="border-b border-[--color-line] px-4 py-4">
        <div class="flex items-center gap-2">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-[--color-brand-700] text-sm font-bold text-white">
                هـ
            </div>
            <div class="min-w-0">
                <div class="truncate text-sm font-semibold leading-tight">
                    {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}
                </div>
                <div class="text-xs text-[--color-ink-soft]">سیستەمی بەڕێوەبردن</div>
            </div>
        </div>
    </div>

    {{-- بەستەرەکان --}}
    <nav class="flex-1 space-y-4 px-3 py-4">
        @foreach ($groups as $group)
            @php
                $visible = collect($group['links'])->filter(
                    fn ($link) => ! $link['can'] || auth()->user()->can($link['can'])
                );
            @endphp

            @if ($visible->isNotEmpty())
                <div>
                    @if ($group['label'])
                        <div class="mb-1 px-2 text-xs font-medium text-[--color-ink-soft]">{{ $group['label'] }}</div>
                    @endif

                    @foreach ($visible as $link)
                        @php $active = request()->routeIs(str_replace('.index', '.*', $link['route'])); @endphp
                        <a href="{{ route($link['route']) }}"
                           class="block rounded-md px-3 py-2 text-sm transition-colors
                                  {{ $active
                                      ? 'bg-[--color-brand-700] font-medium text-white'
                                      : 'text-[--color-ink] hover:bg-[--color-canvas]' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    {{-- بەکارهێنەر --}}
    <div class="border-t border-[--color-line] p-3">
        <div class="mb-2 px-2">
            <div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div>
            <div class="text-xs text-[--color-ink-soft]">
                {{ auth()->user()->isAdmin() ? 'بەڕێوەبەر' : 'بەرپرسی کۆگا' }}
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost w-full">دەرچوون</button>
        </form>
    </div>
</div>
