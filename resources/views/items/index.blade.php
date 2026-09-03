@extends('layouts.app')
@section('title', 'دۆخی کۆگا')

@section('actions')
    @can('manage_items')
        <a href="{{ route('items.create') }}"
           style="background: #2563eb; color: #ffffff; padding: 0.55rem 1.25rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>زیادکردنی مەواد</span>
        </a>
    @endcan
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 1.25rem;">

    {{-- ١. سەردێڕی سەرەوە --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">دۆخی کۆگا</h1>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            @can('manage_items')
                <a href="{{ route('items.create') }}"
                   class="sm:hidden"
                   style="background: #2563eb; color: #ffffff; padding: 0.5rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.8rem; text-decoration: none;">
                    + زیادکردن
                </a>
            @endcan
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکانی سەرەوە (٤ ستوون) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        {{-- ١. کۆی جۆری مەوادەکان --}}
        <div style="background: #f0f9ff; border: 1.5px solid #7dd3fc; border-radius: 1rem; padding: 1.15rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #0284c7; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #075985;">کۆی جۆری مەوادەکان</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #0369a1; line-height: 1.2;">
                {{ fmt_num($totalItemsCount) }} <span style="font-size: 0.8rem; font-weight: 700;">مەواد</span>
            </div>
        </div>

        {{-- ٢. کۆی بڕی بەردەست لە کۆگا --}}
        <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 1rem; padding: 1.15rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #16a34a; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #166534;">کۆی بڕی بەردەست</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #15803d; line-height: 1.2;">
                {{ fmt_num($totalStockQty) }} <span style="font-size: 0.8rem; font-weight: 700;">دانە / تەن</span>
            </div>
        </div>

        {{-- ٣. مەوادی کەمبووەوە --}}
        <div style="background: {{ $lowStockCount > 0 ? '#fff1f2' : '#f8fafc' }}; border: 1.5px solid {{ $lowStockCount > 0 ? '#fecdd3' : '#e2e8f0' }}; border-radius: 1rem; padding: 1.15rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: {{ $lowStockCount > 0 ? '#e11d48' : '#94a3b8' }}; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: {{ $lowStockCount > 0 ? '#9f1239' : '#64748b' }};">مەوادی کەمبووەوە</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: {{ $lowStockCount > 0 ? '#dc2626' : '#64748b' }}; line-height: 1.2;">
                {{ fmt_num($lowStockCount) }} <span style="font-size: 0.8rem; font-weight: 700;">مەواد</span>
            </div>
        </div>

        {{-- ٤. کۆی بەهای مەوادی کۆگا --}}
        @can('view_reports')
            <div style="background: #faf5ff; border: 1.5px solid #d8b4fe; border-radius: 1rem; padding: 1.15rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                <div style="color: #9333ea; margin-bottom: 0.15rem;">
                    <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #6b21a8;">کۆی بەهای کۆگا</div>
                <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #7e22ce; line-height: 1.2;">
                    {{ fmt_num($totalInventoryValue) }} <span style="font-size: 0.8rem; font-weight: 700;">دینار</span>
                </div>
            </div>
        @else
            <div style="background: #fefce8; border: 1.5px solid #fde047; border-radius: 1rem; padding: 1.15rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                <div style="color: #ca8a04; margin-bottom: 0.15rem;">
                    <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #854d0e;">دۆخی گشتی</div>
                <div style="font-size: 1.1rem; font-weight: 800; color: #a16207;">
                    چالاک و ڕێکخراو
                </div>
            </div>
        @endcan

    </div>

    {{-- ٣. کارتی فلتەرکردنی مەوادەکان --}}
    <div style="background: #ffffff; border-radius: 1.15rem; padding: 1.25rem 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <form method="GET" action="{{ route('items.index') }}" style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">

                {{-- ناوی مەواد --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📦</span>
                        <span>ناوی مەواد</span>
                    </label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="field"
                           style="width: 100%; font-weight: 600;"
                           placeholder="ناوی مەواد بنووسە...">
                </div>

                {{-- فلتەری بڕ --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📊</span>
                        <span>فلتەری بڕ</span>
                    </label>
                    <select name="qty_filter" class="field" style="width: 100%; font-weight: 600;">
                        <option value="">هەموو مەوادەکان</option>
                        <option value="qty_desc" @selected(request('qty_filter') === 'qty_desc' || request('sort') === 'qty_desc')>مەوادی زۆر (زۆرترین بڕ)</option>
                        <option value="qty_asc" @selected(request('qty_filter') === 'qty_asc' || request('sort') === 'qty_asc')>مەوادی کەم (کەمترین بڕ)</option>
                    </select>
                </div>

                {{-- ڕیزبەندی --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>⇅</span>
                        <span>ڕیزبەندی بەپێی</span>
                    </label>
                    <select name="order" class="field" style="width: 100%; font-weight: 600;">
                        <option value="">ئاسایی (بەپێی ناو)</option>
                        <option value="latest" @selected(request('order') === 'latest')>نوێترین تۆمارکراو</option>
                        <option value="cost_desc" @selected(request('order') === 'cost_desc')>بەرزترین نرخ</option>
                        <option value="cost_asc" @selected(request('order') === 'cost_asc')>نزمترین نرخ</option>
                    </select>
                </div>

                {{-- دوگمەی فلتەر و پاککردنەوە --}}
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button type="submit"
                            style="background: #2563eb; color: #ffffff; padding: 0.6rem 1.35rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                        <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        <span>فلتەرکردن</span>
                    </button>

                    @if(request()->hasAny(['q', 'qty_filter', 'sort', 'order']))
                        <a href="{{ route('items.index') }}"
                           style="background: #f8fafc; border: 1px solid #cbd5e1; color: #64748b; padding: 0.55rem 0.85rem; border-radius: 0.65rem; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;"
                           title="پاککردنەوە">
                            ✕
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>

    {{-- ٤. خشتەی مەوادەکانی کۆگا --}}
    <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #fafcff; color: #64748b; font-size: 0.78rem; font-weight: 700;">
                        <th style="padding: 0.9rem 1.25rem; text-align: right;">ناوی مەواد</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بڕی بەردەست</th>
                        @can('view_reports')
                            <th style="padding: 0.9rem 1.25rem; text-align: center;">تێچووی کڕین / نرخ</th>
                        @endcan
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بەرواری کڕین</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">کردار</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $isLow = (float) $item->min_qty > 0 && (float) $item->stock_qty <= (float) $item->min_qty;
                        @endphp
                        <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                            onmouseover="this.style.background='#fbfcfd'"
                            onmouseout="this.style.background='transparent'">

                            {{-- ناوی مەواد بە ئایکۆن --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: right;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.65rem; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #dbeafe;">
                                        @if ($item->imageUrl())
                                            <img src="{{ $item->imageUrl() }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.65rem;" alt="{{ $item->name }}">
                                        @else
                                            <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: #1e293b; font-size: 0.92rem;">
                                            {{ $item->name }}
                                        </div>
                                        @if ($item->unit)
                                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 0.1rem;">
                                                یەکە: {{ $item->unit->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- بڕی بەردەست --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center;">
                                <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 0.2rem;">
                                    <span class="num" style="font-size: 1.15rem; font-weight: 800; color: {{ $isLow ? '#dc2626' : '#15803d' }};">
                                        {{ fmt_num($item->stock_qty ?? 0) }}
                                    </span>
                                    <span style="font-size: 0.72rem; font-weight: 700; color: #64748b;">
                                        {{ $item->unit?->name ?? 'دانە' }}
                                    </span>
                                    @if ($isLow)
                                        <span style="background: #fee2e2; color: #dc2626; padding: 0.15rem 0.5rem; border-radius: 0.4rem; font-size: 0.68rem; font-weight: 800;">
                                            کەمبووەتەوە
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- تێچووی کڕین / نرخ --}}
                            @can('view_reports')
                                <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; font-weight: 800; font-size: 0.92rem; color: #334155;">
                                    @if ($item->last_cost > 0)
                                        {{ fmt_num($item->last_cost) }} <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">د.ع</span>
                                    @else
                                        <span style="color: #94a3b8;">—</span>
                                    @endif
                                </td>
                            @endcan

                            {{-- بەرواری کڕین --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; color: #475569; font-size: 0.85rem; font-weight: 600;">
                                @if ($item->purchase_date)
                                    {{ fmt_date($item->purchase_date) }}
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>

                            {{-- کردار --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.45rem;">
                                    @can('manage_items')
                                        <a href="{{ route('items.edit', $item) }}"
                                           style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid #bfdbfe;"
                                           title="دەستکاری">
                                            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم مەوادە؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: #fff1f2; color: #e11d48; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #fecdd3; cursor: pointer;"
                                                    title="سڕینەوە">
                                                <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem;">
                                هیچ مەوادێک نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- لاپەڕەبەندی (Pagination) --}}
        @if ($items->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
