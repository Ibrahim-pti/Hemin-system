@extends('layouts.app')
@section('title', 'وەستا و حەمەڵەکان')

@section('content')
<div class="space-y-6">

    {{-- ١. سەردێڕی وەستا و حەمەڵەکان --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shadow-xs">
                👷
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800">لیستی وەستاکان، حەمەڵەکان و کارمەندانی کارگە</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    کۆی گشتی: {{ $employees->count() }} کەسی چالاک (زیادکردن و بەڕێوەبردن لە ئۆفیسی بەڕێوەبەرەوەیە)
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100/80 px-3.5 py-2 rounded-xl border border-slate-200">
            <span>📅 ئەمڕۆ: {{ date('Y/m/d') }}</span>
        </div>
    </div>

    {{-- ٢. کارتەکانی وەستا و کارمەندان --}}
    @if ($employees->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xs">
            <div class="text-4xl mb-2.5">👷‍♂️</div>
            <div class="font-bold text-slate-700 text-base">هیچ وەستا یان کارمەندێک تۆمار نەکراوە</div>
            <div class="text-xs text-slate-400 mt-1">بەڕێوەبەر لە بەشی کارمەندان دەتوانێت وەستا و حەمەڵەکان زیاد بکات.</div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($employees as $emp)
                @php
                    $todayAtt = $emp->attendances->first();
                    $roleIcons = [
                        'master' => '⚒️',
                        'porter' => '📦',
                        'helper' => '🤝',
                        'driver' => '🚚',
                        'other' => '👤',
                    ];
                    $roleColors = [
                        'master' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'porter' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'helper' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'driver' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'other' => 'bg-slate-100 text-slate-700 border-slate-200',
                    ];
                @endphp
                <div class="bg-white rounded-2xl p-4.5 border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold border flex items-center gap-1.5 {{ $roleColors[$emp->job_title] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                <span>{{ $roleIcons[$emp->job_title] ?? '👤' }}</span>
                                <span>{{ $emp->job_title_label }}</span>
                            </span>

                            {{-- دۆخی ئامادەبوونی ئەمڕۆ --}}
                            @if ($todayAtt)
                                @if ($todayAtt->status === 'present')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        <span>ئامادەیە</span>
                                    </span>
                                @elseif ($todayAtt->status === 'absent')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        <span>نەهاتووە</span>
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        {{ $todayAtt->status }}
                                    </span>
                                @endif
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    چالاکە
                                </span>
                            @endif
                        </div>

                        <h4 class="font-black text-slate-800 text-base mb-1.5">{{ $emp->name }}</h4>
                        
                        @if ($emp->phone)
                            <a href="tel:{{ $emp->phone }}" class="text-xs text-blue-600 hover:text-blue-700 font-mono font-medium flex items-center gap-1.5 direction-ltr text-right mb-2">
                                <span>📞</span>
                                <span dir="ltr">{{ $emp->phone }}</span>
                            </a>
                        @else
                            <div class="text-xs text-slate-400 font-medium mb-2">ژمارەی مۆبایل نییە</div>
                        @endif

                        @if ($emp->note)
                            <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-100 line-clamp-2 mt-2">
                                {{ $emp->note }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                        <span>دەستپێکی کار:</span>
                        <span class="font-medium text-slate-700">{{ $emp->hire_date?->format('Y/m/d') ?? '—' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
