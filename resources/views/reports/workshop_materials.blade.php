@extends('layouts.app')
@section('title', $title)

@section('content')

@include('reports._filter')

{{-- ١. کارتە ئامارییەکان --}}
<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'کۆی جارەکانی بەکارهێنان (سەرفیات)', 'value' => fmt_num($consumedCount), 'tone' => 'warn'])
    @include('partials.stat-tile', ['label' => 'کۆی جارەکانی زیادکردن (هاتوو)', 'value' => fmt_num($receivedCount), 'tone' => 'ok'])
    @include('partials.stat-tile', ['label' => 'کۆی مەوادی بەردەست لە کارگە', 'value' => fmt_num($materials->count()), 'tone' => 'brand'])
    @include('partials.stat-tile', ['label' => 'مەوادە کەمبووەکانی کارگە', 'value' => fmt_num($materials->filter(fn($m) => $m->is_low)->count()), 'tone' => 'danger'])
</div>

{{-- ٢. پوختەی سەرفیات بەپێی مەواد --}}
@if($consumedByMaterial->isNotEmpty())
<div class="card mb-4">
    <div class="card-head flex items-center justify-between">
        <span>پوختەی مەوادە بەکارهاتووەکان (سەرفیات) بۆ دروستکردن</span>
        <span class="text-xs text-[--color-ink-soft]">لەماوەی دیاریکراودا</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ناوی مەواد</th>
                    <th class="num">جارەکانی سەرفکردن</th>
                    <th class="num">کۆی بڕی بەکارهاتوو</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($consumedByMaterial as $row)
                    <tr>
                        <td class="font-bold text-slate-800">{{ $row['item_name'] }}</td>
                        <td class="num">{{ fmt_num($row['count']) }} جار</td>
                        <td class="num font-mono font-bold text-rose-600">{{ fmt_num($row['qty']) }} {{ $row['unit_name'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ٣. خشتەی هەموو جووڵەکانی مەخزەن لەم ماوەیەدا --}}
<div class="card">
    <div class="card-head flex items-center justify-between">
        <span>تۆماری جووڵەکان (بەکارهێنان و هاتنی مەواد)</span>
        <span class="text-xs text-[--color-ink-soft]">کۆگای: {{ $workshopWarehouse?->name ?? 'کۆگای کارگە' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>بەروار</th>
                    <th>مەواد</th>
                    <th>جۆری جووڵە</th>
                    <th class="num">بڕ</th>
                    <th>بۆ وەسڵی داواکاری</th>
                    <th>تێبینی / هۆکار</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $m)
                    <tr>
                        <td class="num whitespace-nowrap">{{ fmt_date($m->moved_at) }}</td>
                        <td class="font-bold text-slate-900">{{ $m->item?->name ?? 'نەناسراو' }}</td>
                        <td>
                            @if($m->direction === 'out')
                                <span class="badge badge-warn">بەکارهاتوو / سەرفیات 📤</span>
                            @else
                                <span class="badge badge-ok">زیادکراو / هاتوو 📥</span>
                            @endif
                        </td>
                        <td class="num font-mono font-bold {{ $m->direction === 'out' ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $m->direction === 'out' ? '-' : '+' }}{{ fmt_num($m->qty) }} {{ $m->item?->unit?->name }}
                        </td>
                        <td>
                            @php
                                $refOrder = $m->reference instanceof \App\Models\Order ? $m->reference : null;
                            @endphp
                            @if($refOrder)
                                <a href="{{ route('orders.show', $refOrder) }}" class="text-[--color-brand-700] hover:underline font-mono text-xs">
                                    وەسڵی #{{ $refOrder->id }} ({{ $refOrder->customer?->name ?: 'کڕیار' }})
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-500">{{ $m->reason ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">
                            هیچ جووڵەیەکی مەخزەن لەم ماوەیەدا تۆمار نەکراوە.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
