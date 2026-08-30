@extends('layouts.app')
@section('title', 'حەقدی و پارەدانەکان')

@section('actions')
    <div style="display: flex; align-items: center; gap: 0.6rem;">
        <a href="{{ route('payments.create', ['type' => 'in']) }}"
           style="background: #10b981; color: #ffffff; padding: 0.6rem 1.25rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
            <span>📥</span>
            <span>وەرگرتنی پارە (حەقدی)</span>
        </a>
        <a href="{{ route('payments.create', ['type' => 'out']) }}"
           style="background: #e11d48; color: #ffffff; padding: 0.6rem 1.25rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);">
            <span>📤</span>
            <span>دانی پارە (خەرجی)</span>
        </a>
    </div>
@endsection

@section('content')

<div x-data="{ showDeleteModal: false, deleteUrl: '' }" style="display: flex; flex-direction: column; gap: 1.25rem; width: 100%;">

    {{-- ١. کارتەکانی کورتە-ئاماری دارایی هاوشێوەی بەشی قاسە --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
        {{-- ١. کۆی وەرگیراو --}}
        <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #16a34a; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #166534;">کۆی وەرگیراو (داهات)</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #15803d; line-height: 1.2;">
                +{{ fmt_money($totalIn) }}
            </div>
        </div>

        {{-- ٢. کۆی دراو --}}
        <div style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #e11d48; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="17 14 12 9 7 14"/>
                    <line x1="12" y1="9" x2="12" y2="21"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #9f1239;">کۆی پارەی دراو (خەرجی)</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #dc2626; line-height: 1.2;">
                -{{ fmt_money($totalOut) }}
            </div>
        </div>

        {{-- ٣. پوختەی جیاوازی --}}
        @php
            $netDiff = $totalIn - $totalOut;
        @endphp
        <div style="background: {{ $netDiff >= 0 ? '#f0f9ff' : '#fff1f2' }}; border: 1.5px solid {{ $netDiff >= 0 ? '#7dd3fc' : '#fecdd3' }}; border-radius: 1rem; padding: 1.15rem 1.1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: {{ $netDiff >= 0 ? '#0284c7' : '#e11d48' }}; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v8M8 12h8"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: {{ $netDiff >= 0 ? '#075985' : '#9f1239' }};">پوختەی جیاوازی (داهات - خەرجی)</div>
            <div class="num" style="font-size: 1.45rem; font-weight: 900; color: {{ $netDiff >= 0 ? '#0369a1' : '#dc2626' }}; line-height: 1.2;">
                {{ fmt_money($netDiff) }}
            </div>
        </div>
    </div>

    {{-- ٢. فۆرمی فلتەر و گەڕان بە دیزاینی خاوێن --}}
    <form method="GET" style="background: #ffffff; border-radius: 1rem; padding: 1rem 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 0.75rem; align-items: flex-end;">
            {{-- گەڕان --}}
            <div style="grid-column: span 2;">
                <label style="font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem; display: block;">گەڕان</label>
                <div style="position: relative;">
                    <input type="search" name="q" value="{{ request('q') }}" class="field"
                           style="width: 100%; padding: 0.55rem 2rem 0.55rem 0.75rem; font-size: 0.825rem; border-radius: 0.6rem;"
                           placeholder="ژمارەی سەنەد، ناوی لایەن یان تێبینی...">
                    <span style="position: absolute; right: 0.7rem; top: 0.65rem; color: #94a3b8; font-size: 0.85rem;">🔍</span>
                </div>
            </div>

            {{-- جۆری جوڵە --}}
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem; display: block;">جۆری جووڵە</label>
                <select name="direction" class="field" style="width: 100%; font-size: 0.825rem; padding: 0.55rem 0.75rem; border-radius: 0.6rem;">
                    <option value="">هەموو جۆرەکان</option>
                    <option value="in" @selected(request('direction') === 'in')>📥 وەرگرتن (داهات)</option>
                    <option value="out" @selected(request('direction') === 'out')>📤 دان (خەرجی)</option>
                </select>
            </div>

            {{-- لە بەرواری --}}
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem; display: block;">لە بەرواری</label>
                <input type="date" name="from" value="{{ request('from') }}" class="field num" style="width: 100%; font-size: 0.825rem; padding: 0.55rem 0.75rem; border-radius: 0.6rem;">
            </div>

            {{-- تا بەرواری --}}
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem; display: block;">تا بەرواری</label>
                <input type="date" name="to" value="{{ request('to') }}" class="field num" style="width: 100%; font-size: 0.825rem; padding: 0.55rem 0.75rem; border-radius: 0.6rem;">
            </div>

            {{-- دوگمەی پاڵاوتن --}}
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <button type="submit"
                        style="background: #2563eb; color: #ffffff; padding: 0.58rem 1.25rem; border-radius: 0.6rem; font-weight: 800; font-size: 0.825rem; border: none; cursor: pointer;">
                    پاڵاوتن
                </button>
                @if(request()->hasAny(['q', 'direction', 'from', 'to']))
                    <a href="{{ route('payments.index') }}"
                       style="padding: 0.58rem 0.75rem; border-radius: 0.6rem; background: #f1f5f9; color: #64748b; font-weight: 700; font-size: 0.825rem; text-decoration: none;"
                       title="پاککردنەوە">
                        ✕
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ٣. خشتەی سەرەکی بە دیزاینی ئارام و خاوێن هاوشێوەی بەشی قاسە --}}
    <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
        <div style="padding: 1.1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b;">
                <span>💳</span>
                <span>تۆماری حەقدی و پارەدانەکان</span>
            </div>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;" class="num">
                کۆی گشتی: {{ fmt_num($payments->total()) }} جووڵە
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #fafcff; color: #64748b; font-size: 0.78rem; font-weight: 700;">
                        <th style="padding: 0.9rem 1.25rem; text-align: center; width: 4rem;">#</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">ژمارەی سەنەد</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بەروار</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">جۆری جووڵە</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: right;">کەس / لایەن</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: right;">تێبینی و پەیوەندی</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center;">بڕی پارە</th>
                        <th style="padding: 0.9rem 1.25rem; text-align: center; width: 7rem;">کردار</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $index => $payment)
                        @php
                            $isIn = $payment->direction === 'in';
                        @endphp
                        <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                            onmouseover="this.style.background='#fbfcfd'"
                            onmouseout="this.style.background='transparent'">

                            {{-- # --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; color: #94a3b8; font-weight: 600; font-size: 0.8rem;">
                                {{ $payments->firstItem() + $index }}
                            </td>

                            {{-- ژمارەی سەنەد --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center;">
                                <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                   style="font-family: monospace; font-weight: 700; color: #334155; text-decoration: none; background: #f1f5f9; padding: 0.2rem 0.55rem; border-radius: 0.4rem; font-size: 0.78rem;"
                                   title="چاپی سەنەد">
                                    {{ $payment->voucher_no }}
                                </a>
                            </td>

                            {{-- بەروار --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; color: #475569; font-weight: 600; font-size: 0.825rem; white-space: nowrap;">
                                {{ fmt_date($payment->paid_at) }}
                            </td>

                            {{-- جۆری جووڵە --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center; white-space: nowrap;">
                                @if ($isIn)
                                    <span style="background: #dcfce7; color: #16a34a; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 800; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span>📥</span>
                                        <span>وەرگرتن (داهات)</span>
                                    </span>
                                @else
                                    <span style="background: #fee2e2; color: #dc2626; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 800; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span>📤</span>
                                        <span>دان (خەرجی)</span>
                                    </span>
                                @endif
                            </td>

                            {{-- کەس / لایەن --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: right; font-weight: 700; color: #1e293b;">
                                @if ($payment->party instanceof \App\Models\Customer)
                                    <a href="{{ route('customers.show', $payment->party) }}" style="color: #1e293b; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#1e293b'">
                                        {{ $payment->party_label }}
                                    </a>
                                @elseif ($payment->party instanceof \App\Models\Supplier)
                                    <a href="{{ route('suppliers.show', $payment->party) }}" style="color: #1e293b; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#1e293b'">
                                        {{ $payment->party_label }}
                                    </a>
                                @else
                                    <span>{{ $payment->party_label }}</span>
                                @endif
                            </td>

                            {{-- تێبینی و بەستنەوە بە وەسڵ/پسوولە --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: right; color: #475569; font-size: 0.825rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    @if ($payment->order)
                                        <a href="{{ route('orders.print', $payment->order) }}" target="_blank"
                                           style="font-family: monospace; font-size: 0.72rem; font-weight: 700; background: #eff6ff; color: #1d4ed8; padding: 0.15rem 0.45rem; border-radius: 0.35rem; text-decoration: none; border: 1px solid #bfdbfe;">
                                            📄 وەسڵی {{ $payment->order->invoice_no }}
                                        </a>
                                    @elseif ($payment->purchase)
                                        <a href="{{ route('purchases.show', $payment->purchase) }}"
                                           style="font-family: monospace; font-size: 0.72rem; font-weight: 700; background: #faf5ff; color: #7e22ce; padding: 0.15rem 0.45rem; border-radius: 0.35rem; text-decoration: none; border: 1px solid #e9d5ff;">
                                            🛒 پسوولەی {{ $payment->purchase->invoice_no }}
                                        </a>
                                    @endif
                                    <span>{{ $payment->note ?: '—' }}</span>
                                </div>
                            </td>

                            {{-- بڕی پارە --}}
                            <td class="num" style="padding: 0.9rem 1.25rem; text-align: center; font-weight: 900; font-size: 1.05rem; color: {{ $isIn ? '#15803d' : '#dc2626' }};">
                                {{ $isIn ? '+' : '-' }}{{ fmt_money($payment->amount, $payment->currency) }}
                            </td>

                            {{-- کردار --}}
                            <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                                    <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                       style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; padding: 0.25rem 0.55rem; border-radius: 0.45rem; font-weight: 700; font-size: 0.75rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem;"
                                       title="چاپکردنی سەنەد">
                                        <span>🖨️</span>
                                        <span>چاپ</span>
                                    </a>
                                    <button type="button"
                                            @click="showDeleteModal = true; deleteUrl = '{{ route('payments.destroy', $payment) }}'"
                                            style="background: #fff1f2; border: 1px solid #fecdd3; color: #e11d48; padding: 0.25rem 0.5rem; border-radius: 0.45rem; font-weight: 700; font-size: 0.75rem; cursor: pointer;"
                                            title="سڕینەوە">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 3rem; text-align: center; color: #94a3b8; font-size: 0.875rem; font-weight: 600;">
                                هیچ حەقدی و پارەدانێک نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    {{-- مۆداڵی دڵنیابوونەوە لە سڕینەوە --}}
    <div x-show="showDeleteModal" x-cloak style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(2px);"
         x-transition.opacity>
        <div style="background: #ffffff; border-radius: 1.25rem; max-width: 24rem; width: 100%; padding: 1.5rem; text-align: center; border: 1px solid #f1f5f9; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);"
             @click.away="showDeleteModal = false"
             x-transition.scale>
            <div style="width: 3.5rem; height: 3.5rem; border-radius: 9999px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto; font-size: 1.5rem;">
                ⚠️
            </div>
            <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">دڵنیایت لە سڕینەوە؟</h3>
            <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 1.25rem; line-height: 1.4;">
                ئەم حەقدییە و جوڵەی ناو قاسەکەی بە تەواوی دەسڕدرێنەوە.
            </p>
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: #dc2626; color: #ffffff; padding: 0.55rem 1.25rem; border-radius: 0.6rem; font-size: 0.8rem; font-weight: 800; border: none; cursor: pointer;">
                        بەڵێ، بسڕەوە
                    </button>
                </form>
                <button type="button" @click="showDeleteModal = false" style="background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; padding: 0.55rem 1.25rem; border-radius: 0.6rem; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                    پاشگەزبوونەوە
                </button>
            </div>
        </div>
    </div>

</div>

@endsection
