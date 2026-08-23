@extends('layouts.app')
@section('title', 'جەردی کۆگا')

@section('content')
<div x-data="{ showDeleteModal: false, deleteUrl: '' }" style="display: flex; flex-direction: column; gap: 1.25rem;">

    {{-- ١. سەردێڕی سەرەوە --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="2"/>
                    <path d="M9 14l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">جەردی کۆگا</h1>
                <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin: 0.15rem 0 0 0;">
                    بەراوردکردنی بڕی واقیعی کاڵاکان لەگەڵ سیستەم و ڕێکخستنەوەی کۆگا
                </p>
            </div>
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکانی سەرەوە (٣ ستوون) --}}
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">

        {{-- ١. کۆی گشتی جەردەکان --}}
        <div style="background: #f0f9ff; border: 1.5px solid #7dd3fc; border-radius: 1rem; padding: 1.15rem 1.25rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #0284c7; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="2"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #075985;">کۆی گشتی جەردەکان</div>
            <div class="num" style="font-size: 1.5rem; font-weight: 900; color: #0369a1; line-height: 1.2;">
                {{ fmt_num($totalCounts) }} <span style="font-size: 0.8rem; font-weight: 700;">جەرد</span>
            </div>
        </div>

        {{-- ٢. جەردە پەسەندکراوەکان --}}
        <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 1rem; padding: 1.15rem 1.25rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #16a34a; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #166534;">جەردی پەسەندکراو و تۆمارکراو</div>
            <div class="num" style="font-size: 1.5rem; font-weight: 900; color: #15803d; line-height: 1.2;">
                {{ fmt_num($postedCounts) }} <span style="font-size: 0.8rem; font-weight: 700;">تەواوکراو</span>
            </div>
        </div>

        {{-- ٣. جەرد لە کاردا / ڕەشنووس --}}
        <div style="background: #fefce8; border: 1.5px solid #fde047; border-radius: 1rem; padding: 1.15rem 1.25rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #ca8a04; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #854d0e;">لە کاردا / ڕەشنووس</div>
            <div class="num" style="font-size: 1.5rem; font-weight: 900; color: #b45309; line-height: 1.2;">
                {{ fmt_num($draftCounts) }} <span style="font-size: 0.8rem; font-weight: 700;">جەرد</span>
            </div>
        </div>

    </div>

    {{-- ٣. کارتی دەستپێکردنی جەردێکی نوێ --}}
    <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
        <div style="padding: 1.1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #1e293b;">
                <span style="color: #2563eb;">⚡</span>
                <span>دەستپێکردنی جەردێکی نوێ</span>
            </div>
        </div>

        <form method="POST" action="{{ route('counts.store') }}" style="padding: 1.5rem;">
            @csrf
            <div style="display: grid; grid-template-columns: 1.5fr 1fr 2fr auto; gap: 1.25rem; align-items: flex-end;">

                {{-- کۆگا --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>🏢</span>
                        <span>کۆگا</span>
                    </label>
                    <select name="warehouse_id" class="field" required style="width: 100%; font-weight: 700; padding: 0.7rem 0.85rem;">
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected($warehouse->is_default)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- بەروار --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📅</span>
                        <span>بەروار</span>
                    </label>
                    <input type="date" name="count_date" class="field num" required value="{{ now()->toDateString() }}" style="width: 100%; padding: 0.7rem 0.85rem; font-weight: 600;">
                </div>

                {{-- تێبینی --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📝</span>
                        <span>تێبینی (ئارەزوومەندانە)</span>
                    </label>
                    <input name="note" class="field" placeholder="تێبینی بنووسە..." style="width: 100%; padding: 0.7rem 0.85rem;">
                </div>

                {{-- دوگمەی دەستپێکردن --}}
                <div>
                    <button type="submit"
                            style="background: #2563eb; color: #ffffff; padding: 0.7rem 1.75rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                        <span>▶</span>
                        <span>دەستپێکردن</span>
                    </button>
                </div>

            </div>

            {{-- ڕێنمایی خوارەوەی فۆڕم --}}
            <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px dashed #e2e8f0; font-size: 0.78rem; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">
                <span style="color: #0284c7;">ℹ️</span>
                <span>کاتی دەستپێکردن، ژمارەی ئێستای هەموو کاڵاکانی کۆگاکە وەک «ژمارەی سیستەم» تۆمار دەکرێت.</span>
            </div>
        </form>
    </div>

    {{-- ٤. خشتەی جەردەکانی پێشوو --}}
    <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
        <div style="padding: 1.1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b;">
                <span>📋</span>
                <span>جەردەکانی پێشوو</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #fafcff; color: #64748b; font-size: 0.78rem; font-weight: 700;">
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">ژمارەی جەرد</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بەروار</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: right;">کۆگا</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">دۆخی جەرد</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: right;">تێبینی</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بەکارهێنەر</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">کردار</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($counts as $count)
                        <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                            onmouseover="this.style.background='#fbfcfd'"
                            onmouseout="this.style.background='transparent'">

                            {{-- ژمارەی جەرد --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center;">
                                <a href="{{ route('counts.show', $count) }}"
                                   style="background: #eff6ff; color: #2563eb; padding: 0.2rem 0.6rem; border-radius: 0.45rem; font-weight: 800; font-size: 0.8rem; text-decoration: none; border: 1px solid #bfdbfe;">
                                    {{ $count->count_no ?? 'SC-' . str_pad($count->id, 4, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>

                            {{-- بەروار --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; color: #475569; font-weight: 600; font-size: 0.85rem;">
                                {{ fmt_date($count->count_date) }}
                            </td>

                            {{-- کۆگا --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: right; font-weight: 700; color: #1e293b;">
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <span>🏢</span>
                                    <span>{{ $count->warehouse?->name ?? 'کۆگای سەرەکی' }}</span>
                                </div>
                            </td>

                            {{-- دۆخ --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                @if ($count->status === 'posted')
                                    <span style="background: #dcfce7; color: #16a34a; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 800; font-size: 0.72rem; display: inline-block;">
                                        ✓ پەسەندکراو
                                    </span>
                                @else
                                    <span style="background: #fef9c3; color: #a16207; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 800; font-size: 0.72rem; display: inline-block;">
                                        ⏳ لە کاردا (ڕەشنووس)
                                    </span>
                                @endif
                            </td>

                            {{-- تێبینی --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: right; color: #64748b; font-size: 0.8rem;">
                                {{ $count->note ?: '—' }}
                            </td>

                            {{-- بەکارهێنەر --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center; color: #475569; font-size: 0.82rem; font-weight: 600;">
                                {{ $count->user?->name ?? '—' }}
                            </td>

                            {{-- کردار --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.45rem;">
                                    {{-- دوگمەی بینین / بەردەوامبوون --}}
                                    <a href="{{ route('counts.show', $count) }}"
                                       style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid #bfdbfe;"
                                       title="{{ $count->status === 'posted' ? 'بینینی جەرد' : 'بەردەوامبوون لە جەرد' }}">
                                        <svg style="width: 1.05rem; height: 1.05rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    {{-- دوگمەی سڕینەوە ئەگەر پەسەند نەکرابێت --}}
                                    @if ($count->status !== 'posted')
                                        <button type="button"
                                                @click="deleteUrl = '{{ route('counts.destroy', $count) }}'; showDeleteModal = true"
                                                style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: #fff1f2; color: #e11d48; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #fecdd3; cursor: pointer;"
                                                title="سڕینەوەی جەرد">
                                            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem;">
                                هێشتا هیچ جەردێک ئەنجامنەدراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- پەیجینەیشن --}}
        @if ($counts->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $counts->links() }}
            </div>
        @endif
    </div>

    {{-- ── ٥. مۆداڵی سڕینەوە لە ناوەڕاست ── --}}
    <template x-teleport="body">
        <div x-show="showDeleteModal"
             x-cloak
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);"
             @keydown.escape.window="showDeleteModal = false">

            <div style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 24rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; margin: auto; padding: 1.5rem; text-align: center; display: flex; flex-direction: column; gap: 1rem;"
                 @click.outside="showDeleteModal = false">

                <div style="width: 3.25rem; height: 3.25rem; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <svg style="width: 1.5rem; height: 1.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        <line x1="10" y1="11" x2="10" y2="17"/>
                        <line x1="14" y1="11" x2="14" y2="17"/>
                    </svg>
                </div>

                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin: 0;">ئایا دڵنیایت لە سڕینەوەی ئەم جەردە؟</h3>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 0.25rem 0 0 0;">ئەم کردارە پاشگەزبوونەوەی نییە.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                    <button type="button" @click="showDeleteModal = false"
                            style="padding: 0.6rem 1rem; border-radius: 0.6rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        پاشگەزبوونەوە
                    </button>
                    <form :action="deleteUrl" method="POST" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                style="width: 100%; padding: 0.6rem 1rem; border-radius: 0.6rem; background: #e11d48; color: #ffffff; font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);">
                            بیسڕەوە
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </template>

</div>
@endsection
