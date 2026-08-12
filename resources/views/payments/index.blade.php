@extends('layouts.app')
@section('title', 'حەقدییەکان')

@section('actions')
    <a href="{{ route('payments.create', ['type' => 'in']) }}" class="btn btn-primary">وەرگرتنی پارە</a>
    <a href="{{ route('payments.create', ['type' => 'out']) }}" class="btn btn-ghost">دانی پارە</a>
@endsection

@section('content')

<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-3">
    @include('partials.stat-tile', ['label' => 'کۆی وەرگیراو', 'value' => fmt_money($totalIn), 'tone' => 'ok', 'icon' => 'payments'])
    @include('partials.stat-tile', ['label' => 'کۆی دراو', 'value' => fmt_money($totalOut), 'tone' => 'warn', 'icon' => 'cash'])
    @include('partials.stat-tile', ['label' => 'جیاوازی', 'value' => fmt_money($totalIn - $totalOut), 'tone' => null, 'icon' => 'reports'])
</div>

<form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="label">گەڕان</label>
            <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ژمارەی حەقدی یان ناو...">
        </div>
        <div>
            <label class="label">جۆر</label>
            <select name="direction" class="field">
                <option value="">هەموو</option>
                <option value="in" @selected(request('direction') === 'in')>وەرگرتن</option>
                <option value="out" @selected(request('direction') === 'out')>دان</option>
            </select>
        </div>
        <div>
            <label class="label">لە بەرواری</label>
            <input type="date" name="from" value="{{ request('from') }}" class="field num">
        </div>
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="label">تا بەرواری</label>
                <input type="date" name="to" value="{{ request('to') }}" class="field num">
            </div>
            <button class="btn btn-primary">پاڵاوتن</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ژمارە</th>
                    <th>بەروار</th>
                    <th>جۆر</th>
                    <th>لایەن</th>
                    <th class="num">بڕ</th>
                    <th>قاسە</th>
                    <th>تێبینی</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="num font-medium">{{ $payment->voucher_no }}</td>
                        <td class="num whitespace-nowrap">{{ fmt_date($payment->paid_at) }}</td>
                        <td>
                            <span class="badge {{ $payment->direction === 'in' ? 'badge-ok' : 'badge-warn' }}">
                                {{ $payment->direction === 'in' ? 'وەرگرتن' : 'دان' }}
                            </span>
                        </td>
                        <td>{{ $payment->party_label }}</td>
                        <td class="num font-medium">{{ fmt_money($payment->amount, $payment->currency) }}</td>
                        <td class="text-[--color-ink-soft]">{{ $payment->cashBox?->name ?? '—' }}</td>
                        <td class="text-[--color-ink-soft]">{{ $payment->note ?? '—' }}</td>
                        <td class="whitespace-nowrap text-left">
                            <a href="{{ route('payments.print', $payment) }}" target="_blank"
                               class="text-sm text-[--color-brand-700]">چاپ</a>
                            <button type="submit" form="del-pay-{{ $payment->id }}"
                                    class="mr-2 text-sm text-[--color-danger]"
                                    onclick="return confirm('حەقدی و جوڵەی قاسەکەی دەسڕدرێنەوە. دڵنیایت؟')">سڕینەوە</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ حەقدییەک نەدۆزرایەوە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach ($payments as $payment)
    <form id="del-pay-{{ $payment->id }}" method="POST" action="{{ route('payments.destroy', $payment) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endforeach

<div class="mt-4">{{ $payments->links() }}</div>

@endsection
