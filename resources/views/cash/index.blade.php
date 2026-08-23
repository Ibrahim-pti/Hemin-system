@extends('layouts.app')
@section('title', 'قاسە — حیسابی ڕۆژانە و مێژووی پارە')

@section('content')
<div x-data="{
    showDepositModal: false,
    showWithdrawModal: false,
    depositAmount: '',
    withdrawAmount: '',
}" style="display: flex; flex-direction: column; gap: 1.25rem; width: 100%;">

    {{-- ١. سەردێڕی سەرەوە لەگەڵ دوو دوگمەی کردار --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                    <circle cx="12" cy="12" r="2"/>
                    <path d="M6 12h.01M18 12h.01"/>
                </svg>
            </div>
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">قاسە و حیسابی پارە</h1>
                <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin: 0.15rem 0 0 0;">
                    بەڕێوەبردنی هاتوو و رۆیشتووی پارەی قاسە، فرۆشتن و خەرجییەکان
                </p>
            </div>
        </div>

        {{-- دوو دوگمەی خێرای تێکردن و دەرهێنان --}}
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button"
                    @click="showDepositModal = true"
                    style="background: #10b981; color: #ffffff; padding: 0.6rem 1.25rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                <span>📥</span>
                <span>تێکردنی پارە (داهات)</span>
            </button>

            <button type="button"
                    @click="showWithdrawModal = true"
                    style="background: #e11d48; color: #ffffff; padding: 0.6rem 1.25rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);">
                <span>📤</span>
                <span>دەرهێنانی پارە (خەرجی / کێشکردن)</span>
            </button>
        </div>
    </div>

    {{-- ٢. ٤ کارتی ئاماری سەرەکی --}}
    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem;">

        {{-- ١. باڵانسی ئێستای قاسەی دینار --}}
        @php
            $iqdBox = $boxes->where('currency', 'IQD')->first();
            $iqdBalance = $iqdBox ? $iqdBox->balance() : 0;
            $usdBox = $boxes->where('currency', 'USD')->first();
            $usdBalance = $usdBox ? $usdBox->balance() : 0;
        @endphp
        <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #16a34a; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                    <circle cx="12" cy="12" r="2"/>
                    <path d="M6 12h.01M18 12h.01"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #166534;">باڵانسی ئێستای قاسە (دینار)</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #15803d; line-height: 1.2;">
                {{ fmt_num($iqdBalance) }} <span style="font-size: 0.8rem; font-weight: 700;">دینار</span>
            </div>
        </div>

        {{-- ٢. کۆی پارەی هاتووی ماوەکە --}}
        <div style="background: #f0f9ff; border: 1.5px solid #7dd3fc; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #0284c7; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #075985;">پارەی هاتوو (فرۆشتن + داهات)</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #0369a1; line-height: 1.2;">
                +{{ fmt_num($boxStats->sum('periodIn')) }} <span style="font-size: 0.8rem; font-weight: 700;">دینار</span>
            </div>
        </div>

        {{-- ٣. کۆی پارەی ڕۆیشتووی ماوەکە --}}
        <div style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #e11d48; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="17 14 12 9 7 14"/>
                    <line x1="12" y1="9" x2="12" y2="21"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #9f1239;">پارەی دەرکراو (خەرجی + بردن)</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #dc2626; line-height: 1.2;">
                -{{ fmt_num($boxStats->sum('periodOut')) }} <span style="font-size: 0.8rem; font-weight: 700;">دینار</span>
            </div>
        </div>

        {{-- ٤. باڵانسی دۆلار یان پوختەی ماوەکە --}}
        @if ($usdBox && $usdBalance != 0)
            <div style="background: #faf5ff; border: 1.5px solid #d8b4fe; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                <div style="color: #9333ea; margin-bottom: 0.15rem;">
                    <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #6b21a8;">باڵانسی قاسەی دۆلار</div>
                <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #7e22ce; line-height: 1.2;">
                    ${{ fmt_num($usdBalance) }}
                </div>
            </div>
        @else
            @php $periodNet = $boxStats->sum('periodNet'); @endphp
            <div style="background: {{ $periodNet >= 0 ? '#f0fdf4' : '#fff1f2' }}; border: 1.5px solid {{ $periodNet >= 0 ? '#86efac' : '#fecdd3' }}; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                <div style="color: {{ $periodNet >= 0 ? '#16a34a' : '#e11d48' }}; margin-bottom: 0.15rem;">
                    <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v8M8 12h8"/>
                    </svg>
                </div>
                <div style="font-size: 0.8rem; font-weight: 700; color: {{ $periodNet >= 0 ? '#166534' : '#9f1239' }};">پوختەی جووڵەی ماوەکە</div>
                <div class="num" style="font-size: 1.45rem; font-weight: 900; color: {{ $periodNet >= 0 ? '#15803d' : '#dc2626' }}; line-height: 1.2;">
                    {{ $periodNet >= 0 ? '+' : '' }}{{ fmt_num($periodNet) }} <span style="font-size: 0.8rem; font-weight: 700;">دینار</span>
                </div>
            </div>
        @endif

    </div>

    {{-- ٣. کارتی فلتەرکردنی بەروار و جۆری جووڵە --}}
    <div style="background: #ffffff; border-radius: 1.15rem; padding: 1.25rem 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <form method="GET" action="{{ route('cash.index') }}" style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1.5fr 1.5fr auto; gap: 1rem; align-items: flex-end;">

                {{-- لە بەرواری --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📅</span>
                        <span>لە بەرواری</span>
                    </label>
                    <input type="date" name="from" value="{{ $dateFrom }}" class="field num" style="width: 100%; font-weight: 600;">
                </div>

                {{-- بۆ بەرواری --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📅</span>
                        <span>بۆ بەرواری</span>
                    </label>
                    <input type="date" name="to" value="{{ $dateTo }}" class="field num" style="width: 100%; font-weight: 600;">
                </div>

                {{-- قاسە --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>🏦</span>
                        <span>قاسە</span>
                    </label>
                    <select name="cash_box_id" class="field" style="width: 100%; font-weight: 600;">
                        <option value="">هەموو قاسەکان</option>
                        @foreach ($boxes as $b)
                            <option value="{{ $b->id }}" @selected($boxId == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- جۆری جووڵە --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>⇅</span>
                        <span>جۆری جووڵە</span>
                    </label>
                    <select name="direction" class="field" style="width: 100%; font-weight: 600;">
                        <option value="">هەموو جووڵەکان (هاتوو و ڕۆیشتوو)</option>
                        <option value="in" @selected($direction === 'in')>تەنها پارەی هاتوو (📥 داهات)</option>
                        <option value="out" @selected($direction === 'out')>تەنها پارەی دەرکراو (📤 خەرجی)</option>
                    </select>
                </div>

                {{-- دوگمەی نیشاندان --}}
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button type="submit"
                            style="background: #2563eb; color: #ffffff; padding: 0.65rem 1.4rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                        <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <span>پیشاندان</span>
                    </button>

                    @if(request()->hasAny(['from', 'to', 'cash_box_id', 'direction']))
                        <a href="{{ route('cash.index') }}"
                           style="background: #f8fafc; border: 1px solid #cbd5e1; color: #64748b; padding: 0.6rem 0.85rem; border-radius: 0.65rem; font-size: 0.85rem; font-weight: 700; text-decoration: none;"
                           title="پاککردنەوەی فلتەر">
                            ✕
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>

    {{-- ٤. خشتەی مێژووی جووڵەکانی قاسە --}}
    <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
        <div style="padding: 1.1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b;">
                <span>📋</span>
                <span>تۆماری جووڵەکانی قاسە</span>
            </div>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
                پیشاندانی هەموو تێکردن، دەرهێنان و پارەی فرۆشتن
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #fafcff; color: #64748b; font-size: 0.78rem; font-weight: 700;">
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بەروار</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">جۆری جووڵە</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: right;">کەس / لایەن / مەبەست</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: right;">تێبینی</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بڕی پارە</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">قاسە</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بەکارهێنەر</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $t)
                        @php
                            $isIn = $t->direction === 'in';
                            $ref = $t->reference;
                        @endphp
                        <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                            onmouseover="this.style.background='#fbfcfd'"
                            onmouseout="this.style.background='transparent'">

                            {{-- بەروار --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; color: #475569; font-weight: 600; font-size: 0.85rem;">
                                {{ fmt_date($t->occurred_at) }}
                            </td>

                            {{-- جۆری جووڵە --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                @if ($isIn)
                                    <span style="background: #dcfce7; color: #16a34a; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 800; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span>📥</span>
                                        <span>تێکردن (داهات)</span>
                                    </span>
                                @else
                                    <span style="background: #fee2e2; color: #dc2626; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 800; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span>📤</span>
                                        <span>دەرهێنان (خەرجی)</span>
                                    </span>
                                @endif
                            </td>

                            {{-- کەس / لایەن / مەبەست --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: right; font-weight: 700; color: #1e293b;">
                                @if ($ref && method_exists($ref, 'party') && $ref->party)
                                    <span>{{ $ref->party->name }}</span>
                                @elseif ($t->category_label)
                                    <span>{{ $t->category_label }}</span>
                                @else
                                    <span>—</span>
                                @endif
                            </td>

                            {{-- تێبینی --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: right; color: #64748b; font-size: 0.82rem;">
                                {{ $t->note ?: '—' }}
                            </td>

                            {{-- بڕی پارە --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; font-weight: 900; font-size: 1.05rem; color: {{ $isIn ? '#15803d' : '#dc2626' }};">
                                {{ $isIn ? '+' : '-' }}{{ fmt_num($t->amount) }}
                                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
                                    {{ $t->cashBox?->currency ?? 'IQD' }}
                                </span>
                            </td>

                            {{-- قاسە --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                <span style="background: #f1f5f9; color: #475569; padding: 0.2rem 0.6rem; border-radius: 0.4rem; font-size: 0.75rem; font-weight: 700;">
                                    {{ $t->cashBox?->name ?? 'قاسەی سەرەکی' }}
                                </span>
                            </td>

                            {{-- بەکارهێنەر --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center; color: #475569; font-size: 0.82rem; font-weight: 600;">
                                {{ $t->user?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem;">
                                هیچ جووڵەیەکی پارە لەم ماوەیەدا تۆمارنەکراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- پەیجینەیشن --}}
        @if ($transactions->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- ٥. مۆداڵی تێکردنی پارە (داهات / پارەی دانراو) --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div x-show="showDepositModal"
             x-cloak
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);"
             @keydown.escape.window="showDepositModal = false">
            <div style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 28rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; margin: auto; position: relative;"
                 @click.outside="showDepositModal = false">

                {{-- سەری مۆداڵ بە سەوز --}}
                <div style="padding: 1.1rem 1.5rem; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
                    <button type="button" @click="showDepositModal = false" style="background: none; border: none; font-size: 1.25rem; color: #ffffff; cursor: pointer; line-height: 1;">✕</button>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.05rem;">
                        <span>📥 تێکردنی پارە بۆ ناو قاسە</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('cash.transaction') }}" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
                    @csrf
                    <input type="hidden" name="direction" value="in">

                    {{-- قاسە --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">قاسە</label>
                        <select name="cash_box_id" class="field" style="width: 100%; font-weight: 600;">
                            @foreach ($boxes as $box)
                                <option value="{{ $box->id }}">{{ $box->name }} ({{ $box->currency }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- بڕی پارە --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            بڕی پارە <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" step="any" min="1" name="amount" class="field num" required
                               placeholder="بڕی پارە بە ژمارە بنووسە..."
                               style="width: 100%; padding: 0.65rem 1rem; font-size: 1.25rem; font-weight: 800; text-align: center; color: #15803d;">
                    </div>

                    {{-- ناوی کەس / سەرچاوەی پارە --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            ناوی کەس / سەرچاوەی پارە
                        </label>
                        <input type="text" name="person_name" class="field"
                               placeholder="وەک: ناوی کەسی دانەر، سەرمایەی کارگە، پارەی دەستی..."
                               style="width: 100%;">
                    </div>

                    {{-- بەروار --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            بەروار <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="occurred_at" value="{{ now()->toDateString() }}" class="field num" required style="width: 100%; font-weight: 600;">
                    </div>

                    {{-- تێبینی --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">تێبینی</label>
                        <textarea name="note" rows="2" class="field" placeholder="ڕوونکردنەوەی زیاتر..." style="width: 100%; font-size: 0.85rem;"></textarea>
                    </div>

                    {{-- دوگمەکان --}}
                    <div style="display: flex; gap: 0.6rem; padding-top: 0.5rem;">
                        <button type="submit"
                                style="background: #10b981; color: #ffffff; padding: 0.6rem 1.5rem; border-radius: 0.55rem; font-weight: 800; font-size: 0.875rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);">
                            <span>✓</span>
                            <span>تۆمارکردنی داهات</span>
                        </button>
                        <button type="button" @click="showDepositModal = false"
                                style="padding: 0.6rem 1.25rem; border-radius: 0.55rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.875rem; cursor: pointer;">
                            داخستن
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- ٦. مۆداڵی دەرهێنانی پارە (خەرجی / کێشکردن بۆ کەسێک) --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div x-show="showWithdrawModal"
             x-cloak
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);"
             @keydown.escape.window="showWithdrawModal = false">
            <div style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 28rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; margin: auto; position: relative;"
                 @click.outside="showWithdrawModal = false">

                {{-- سەری مۆداڵ بە سوور --}}
                <div style="padding: 1.1rem 1.5rem; background: #e11d48; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
                    <button type="button" @click="showWithdrawModal = false" style="background: none; border: none; font-size: 1.25rem; color: #ffffff; cursor: pointer; line-height: 1;">✕</button>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.05rem;">
                        <span>📤 دەرهێنانی پارە لە قاسە</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('cash.transaction') }}" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
                    @csrf
                    <input type="hidden" name="direction" value="out">

                    {{-- قاسە --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">قاسە</label>
                        <select name="cash_box_id" class="field" style="width: 100%; font-weight: 600;">
                            @foreach ($boxes as $box)
                                <option value="{{ $box->id }}">{{ $box->name }} ({{ $box->currency }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- بڕی پارە --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            بڕی پارە <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" step="any" min="1" name="amount" class="field num" required
                               placeholder="بڕی پارە بە ژمارە بنووسە..."
                               style="width: 100%; padding: 0.65rem 1rem; font-size: 1.25rem; font-weight: 800; text-align: center; color: #dc2626;">
                    </div>

                    {{-- ناوی کەس / کێ پارەی بردووە --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            ناوی کەس / کێ پارەی بردووە
                        </label>
                        <input type="text" name="person_name" class="field"
                               placeholder="وەک: ناوی کارمەند، خاوەنکار، فرۆشیار، کڕینی کەرەستە..."
                               style="width: 100%;">
                    </div>

                    {{-- هۆکار / پۆلێن --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            هۆکار / بابەت
                        </label>
                        <select name="category" class="field" style="width: 100%; font-weight: 600;">
                            <option value="expense">خەرجی گشتی</option>
                            <option value="other">ڕاکێشانی کەسی / دان بە کەسێک</option>
                            <option value="wage">حەقدەستی کارمەند</option>
                            <option value="supplier_payment">پارەدان بە فرۆشیار</option>
                        </select>
                    </div>

                    {{-- بەروار --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            بەروار <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="occurred_at" value="{{ now()->toDateString() }}" class="field num" required style="width: 100%; font-weight: 600;">
                    </div>

                    {{-- تێبینی --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">تێبینی</label>
                        <textarea name="note" rows="2" class="field" placeholder="ڕوونکردنەوەی زیاتر..." style="width: 100%; font-size: 0.85rem;"></textarea>
                    </div>

                    {{-- دوگمەکان --}}
                    <div style="display: flex; gap: 0.6rem; padding-top: 0.5rem;">
                        <button type="submit"
                                style="background: #e11d48; color: #ffffff; padding: 0.6rem 1.5rem; border-radius: 0.55rem; font-weight: 800; font-size: 0.875rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.3);">
                            <span>✓</span>
                            <span>تۆمارکردنی دەرهێنان</span>
                        </button>
                        <button type="button" @click="showWithdrawModal = false"
                                style="padding: 0.6rem 1.25rem; border-radius: 0.55rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.875rem; cursor: pointer;">
                            داخستن
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
