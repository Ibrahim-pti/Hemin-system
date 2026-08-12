@extends('layouts.app')
@section('title', $title)

@section('content')

@include('reports._filter')

<div class="mx-auto max-w-2xl">

    {{-- ژمارەی سەرەکی --}}
    <div class="card">
        <div class="card-body text-center">
            <div class="text-sm text-[--color-ink-soft]">قازانجی ماوەکە</div>
            <div class="num mt-2 text-4xl font-bold {{ $profit >= 0 ? 'text-[--color-ok]' : 'text-[--color-danger]' }}">
                {{ fmt_money($profit) }}
            </div>
            <div class="num mt-1 text-xs text-[--color-ink-soft]">{{ fmt_date($from) }} — {{ fmt_date($to) }}</div>
        </div>
    </div>

    {{-- وردەکاری --}}
    <div class="card mt-4">
        <div class="card-head">پێکهاتەی حیساب</div>
        <div class="overflow-x-auto">
            <table class="table">
                <tbody>
                    <tr>
                        <td class="font-medium">فرۆشتن</td>
                        <td class="num font-medium text-[--color-ok]">+{{ fmt_money($sales) }}</td>
                    </tr>
                    @foreach ([
                        'کڕینی مەواد' => $purchases,
                        'ئیشی خاریجی' => $jobs,
                        'حەقدەستی کارمەند' => $wages,
                        'خەرجی' => $expenses,
                    ] as $label => $value)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="num text-[--color-danger]">−{{ fmt_money($value) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-[--color-surface-soft]">
                        <td class="text-base font-bold">قازانج</td>
                        <td class="num text-base font-bold {{ $profit >= 0 ? 'text-[--color-ok]' : 'text-[--color-danger]' }}">
                            {{ fmt_money($profit) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <p class="mt-3 text-xs text-[--color-ink-soft]">
        ئەمە قازانجی گشتی کارگەیە بۆ ئەم ماوەیە — نەک قازانجی هەر وەسڵێک بە جیا.
        حەقدەست و خەرجی لە جوڵەکانی قاسەوە دەگیردرێن.
    </p>
</div>

@endsection
