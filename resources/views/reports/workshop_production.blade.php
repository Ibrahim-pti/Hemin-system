@extends('layouts.app')
@section('title', $title)

@section('content')

@include('reports._filter')

{{-- ١. کارتە ئامارییەکانی دروستکردن --}}
<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'کۆی وەسڵەکان', 'value' => fmt_num($totalCount), 'tone' => null])
    @include('partials.stat-tile', ['label' => 'تەواوکراو و ڕادەستکراو', 'value' => fmt_num($deliveredCount), 'tone' => 'ok'])
    @include('partials.stat-tile', ['label' => 'لە دروستکردندا', 'value' => fmt_num($inProductionCount), 'tone' => 'brand'])
    @include('partials.stat-tile', ['label' => 'چاوەڕوانکراو / ئامادە', 'value' => fmt_num($pendingCount + $readyCount), 'tone' => 'warn'])
</div>

{{-- ٢. کۆی کەلوپەلە دروستکراوەکان بەپێی جۆر --}}
@if($itemsBreakdown->isNotEmpty())
<div class="card mb-4">
    <div class="card-head flex items-center justify-between">
        <span>پوختەی کەلوپەلە دروستکراوەکان بەپێی بابەت</span>
        <span class="text-xs text-[--color-ink-soft]">لەماوەی دیاریکراودا</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ناوی کەلوپەل</th>
                    <th class="num">ژمارەی دووبارەبوونەوە</th>
                    <th class="num">کۆی بڕی دروستکراو</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itemsBreakdown as $row)
                    <tr>
                        <td class="font-bold text-slate-800">{{ $row['name'] }}</td>
                        <td class="num">{{ fmt_num($row['count']) }} وەسڵ</td>
                        <td class="num font-mono font-bold text-blue-600">{{ fmt_num($row['qty']) }} {{ $row['unit'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ٣. خشتەی وردەکاری تەواوی وەسڵەکانی کارگە --}}
<div class="card">
    <div class="card-head flex items-center justify-between">
        <span>لیستی وەسڵەکان و دۆخی دروستکردن</span>
        <span class="text-xs text-[--color-ink-soft]">کۆی گشتی: {{ fmt_num($orders->count()) }} وەسڵ</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ژمارەی وەسڵ</th>
                    <th>بەرواری داواکاری</th>
                    <th>بەرواری گەیاندن</th>
                    <th>کڕیار</th>
                    <th>کەلوپەل و قیاسات</th>
                    <th>دۆخی دروستکردن</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="num font-bold">
                            <a href="{{ route('orders.show', $order) }}" class="text-[--color-brand-700] hover:underline font-mono">
                                #{{ $order->invoice_no }}
                            </a>
                        </td>
                        <td class="num whitespace-nowrap">{{ fmt_date($order->order_date) }}</td>
                        <td class="num whitespace-nowrap">
                            @if($order->delivery_date)
                                <span class="font-mono {{ $order->delivery_date->isPast() && $order->status !== 'delivered' ? 'text-rose-600 font-bold' : '' }}">
                                    {{ fmt_date($order->delivery_date) }}
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="font-medium">{{ $order->customer?->name ?? 'نەناسراو' }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach($order->items as $it)
                                    <span class="inline-block bg-slate-100 px-2 py-0.5 rounded text-xs text-slate-800">
                                        {{ $it->item_name }} ({{ fmt_num($it->qty) }} {{ $it->unit_name }})
                                        @if($it->measurement_label && $it->measurement_label !== '—')
                                            <b class="text-blue-600 font-mono">[{{ $it->measurement_label }}]</b>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @if($order->status === 'delivered')
                                <span class="badge badge-ok">ڕادەستکراو ✔️</span>
                            @elseif($order->status === 'ready')
                                <span class="badge badge-ok">ئامادەیە ✅</span>
                            @elseif($order->status === 'in_production')
                                <span class="badge badge-brand">لە دروستکردندا ⚙️</span>
                            @elseif($order->status === 'confirmed')
                                <span class="badge badge-warn">چاوەڕوانە ⏳</span>
                            @else
                                <span class="badge">{{ $order->status_label }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">
                            هیچ وەسڵێک لەم ماوەیەدا نییە.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
