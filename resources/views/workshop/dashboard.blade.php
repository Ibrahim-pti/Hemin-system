@extends('layouts.menu')
@section('title', 'داشبۆردی سەرەکی کارگە')

@section('content')
<div x-data="{
    showStockInModal: false,
    previewImg: null,
}" class="space-y-6">

    {{-- ١. سەردێڕی داشبۆرد --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shadow-xs">
                ⚒️
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-800">داشبۆردی سەرەکی کارگە</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $workshopWarehouse?->name ?? 'شوێنی دروستکردن' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    پوختەی گشتی کارەکانی دروستکردن، ئاگاداری مەوادە کەمبووەکان و دۆخی وەستاکان
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-200">
            <span>📅 ئەمڕۆ: {{ date('Y/m/d') }}</span>
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکان --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- ١. چاوەڕوانە --}}
        <a href="{{ route('workshop.orders', ['tab' => 'pending']) }}"
           class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-amber-400 transition-all hover:shadow-md group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">چاوەڕوانی دروستکردن</span>
                <span class="size-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold border border-amber-200/60 group-hover:scale-110 transition-transform">⏳</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-amber-600">{{ $pendingCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">ئیشی نوێی پەسەندکراو</div>
        </a>

        {{-- ٢. لە کاردایە (دروستدەکرێت) --}}
        <a href="{{ route('workshop.orders', ['tab' => 'in_production']) }}"
           class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-blue-400 transition-all hover:shadow-md group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">لە ژێر کاردایە (دروستدەکرێت)</span>
                <span class="size-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold border border-blue-200/60 group-hover:scale-110 transition-transform">⚙️</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-blue-600">{{ $inProductionCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">وەستاکان کاری لەسەر دەکەن</div>
        </a>

        {{-- ٣. تەواوبوو (ئامادەیە) --}}
        <a href="{{ route('workshop.orders', ['tab' => 'ready']) }}"
           class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-emerald-400 transition-all hover:shadow-md group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">ئامادەیە بۆ وەرگرتن</span>
                <span class="size-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold border border-emerald-200/60 group-hover:scale-110 transition-transform">✅</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-emerald-600">{{ $readyCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">دروستکراوە و ئامادەیە</div>
        </a>

        {{-- ٤. ڕادەستکراو --}}
        <a href="{{ route('workshop.orders', ['tab' => 'delivered']) }}"
           class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-slate-400 transition-all hover:shadow-md group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">ئەمڕۆ ڕادەستکراوە</span>
                <span class="size-9 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center text-base font-bold border border-slate-200/60 group-hover:scale-110 transition-transform">🚚</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-slate-800">{{ $deliveredCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">کارە تەواوکراوەکان</div>
        </a>
    </div>

    {{-- ٣. ئاگاداری مەوادە کەمبووەکان (Low Stock Alert Banner) --}}
    @if ($lowStockMaterials->isNotEmpty())
        <div class="bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 rounded-2xl p-4.5 border border-rose-200 shadow-xs">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-rose-600 text-white font-bold text-base shadow-xs animate-bounce">
                        ⚠️
                    </span>
                    <div>
                        <h3 class="font-black text-sm text-rose-900 flex items-center gap-2">
                            <span>ئاگاداری: مەوادە کەمبووەکانی کارگە</span>
                            <span class="px-2 py-0.2 rounded-full bg-rose-100 text-rose-800 text-[11px] font-black border border-rose-200">
                                {{ $lowStockMaterials->count() }} جۆر مەواد پێویستی بە پڕکردنەوەیە
                            </span>
                        </h3>
                        <p class="text-xs text-rose-700 font-medium mt-0.5">ئەم مەوادانە لە سنووری کەمترین بڕی پێویست کەمتریان ماوە</p>
                    </div>
                </div>
                <a href="{{ route('workshop.materials') }}" class="text-xs font-bold text-rose-800 hover:text-rose-950 underline flex items-center gap-1 cursor-pointer">
                    <span>بینینی هەموو مەوادەکان</span>
                    <span>←</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach ($lowStockMaterials as $lowMat)
                    <div class="bg-white rounded-xl p-3 border border-rose-200/80 flex items-center justify-between gap-2 shadow-2xs hover:border-rose-300 transition-all">
                        <div class="min-w-0 flex-1">
                            <div class="font-black text-xs text-slate-800 truncate" title="{{ $lowMat->name }}">{{ $lowMat->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono font-medium">{{ $lowMat->code }}</div>
                            <div class="text-xs font-black text-rose-600 mt-1 flex items-center gap-1.5">
                                <span>ماوە: {{ fmt_num($lowMat->stock_qty) }} {{ $lowMat->unit?->name }}</span>
                                <span class="text-slate-400 font-normal text-[10px]">(سنوور: {{ fmt_num($lowMat->min_qty) }})</span>
                            </div>
                        </div>
                        <button type="button" @click="showStockInModal = true" class="btn btn-ghost !py-1.5 !px-2.5 text-[11px] font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 shrink-0 cursor-pointer shadow-2xs">
                            + زیادکردن
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ٤. پوختەی دوو ستوونی: کارەکان و وەستاکان --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- ستوونی ڕاست: کارە چالاکەکان --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5 font-black text-sm text-slate-800">
                    <span class="size-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">📋</span>
                    <span>کارە چالاکەکانی لە دروستکردندان</span>
                </div>
                <a href="{{ route('workshop.orders') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                    هەموو داواکارییەکان &larr;
                </a>
            </div>

            <div class="p-4">
                @if ($activeOrders->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs font-bold">هیچ کارێکی چالاک لە چاوەڕوانیدا نییە.</div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        @foreach ($activeOrders as $order)
                            <div class="border border-slate-200 rounded-xl p-3.5 hover:border-blue-300 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-black text-xs text-slate-800">وەسڵی #{{ $order->invoice_no }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $order->status === 'in_production' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            {{ $order->status === 'in_production' ? '⚙️ لە دروستکردندایە' : '⏳ چاوەڕوانە' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-600 font-bold mb-2">
                                        کڕیار: {{ $order->customer?->name ?? 'نەناسراو' }}
                                    </div>

                                    {{-- کەلوپەلەکان --}}
                                    <div class="space-y-1 bg-slate-50 p-2 rounded-lg border border-slate-100 text-[11px]">
                                        @foreach ($order->items->take(2) as $it)
                                            <div class="flex items-center justify-between text-slate-700">
                                                <span class="font-bold truncate">{{ $it->item_name }}</span>
                                                <span class="font-bold text-blue-600 shrink-0">{{ fmt_num($it->qty) }} دانا</span>
                                            </div>
                                        @endforeach
                                        @if ($order->items->count() > 2)
                                            <div class="text-[10px] text-slate-400 font-medium">+ {{ $order->items->count() - 2 }} بڕگەی تر</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                    <form method="POST" action="{{ route('workshop.status', $order) }}">
                                        @csrf
                                        @if ($order->status === 'confirmed')
                                            <input type="hidden" name="status" value="in_production">
                                            <button type="submit" class="btn btn-primary !py-1 !px-2.5 text-[11px] font-black bg-blue-600 hover:bg-blue-700 cursor-pointer">
                                                ⚙️ دەستپێکردن
                                            </button>
                                        @elseif ($order->status === 'in_production')
                                            <input type="hidden" name="status" value="ready">
                                            <button type="submit" class="btn btn-primary !py-1 !px-2.5 text-[11px] font-black bg-emerald-600 hover:bg-emerald-700 cursor-pointer">
                                                ✅ تەواوبوو
                                            </button>
                                        @endif
                                    </form>

                                    <span class="text-[10px] text-slate-400 font-medium">
                                        {{ $order->order_date?->format('Y/m/d') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ستوونی چەپ: وەستاکان --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5 font-black text-sm text-slate-800">
                    <span class="size-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">👷</span>
                    <span>وەستاکانی کارگە</span>
                </div>
                <a href="{{ route('workshop.employees') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                    هەمووی &larr;
                </a>
            </div>

            <div class="p-4 space-y-2.5">
                @forelse ($employees->take(5) as $emp)
                    @php
                        $todayAtt = $emp->attendances->first();
                    @endphp
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 flex items-center justify-between gap-2">
                        <div>
                            <div class="font-bold text-xs text-slate-800">{{ $emp->name }}</div>
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $emp->job_title_label }}</div>
                        </div>
                        <div>
                            @if ($todayAtt && $todayAtt->status === 'present')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">ئامادەیە ✅</span>
                            @elseif ($todayAtt && $todayAtt->status === 'absent')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">نەهاتووە ❌</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-200 text-slate-600">چالاکە</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs font-medium">هیچ وەستایەک تۆمار نەکراوە.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- مۆداڵی زیادکردنی بڕ بۆ مەواد (Stock In) --}}
    <div x-show="showStockInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockInModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">📥</span>
                    <span>هاتنی مەواد بۆ کارگە (زیادکردنی بڕ)</span>
                </div>
                <button type="button" @click="showStockInModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-in') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">هەڵبژاردنی مەواد <span class="text-rose-500">*</span></label>
                    <select name="item_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                        <option value="">-- مەوادەکە هەڵبژێرە --</option>
                        @foreach ($allItems as $it)
                            <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بڕی هاتووە <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="qty" required placeholder="0.00"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono text-base font-bold text-emerald-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" name="note" placeholder="بۆ نموونە: کڕین لە بازاڕ، گواستنەوە..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showStockInModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700">تۆمارکردنی هاتن</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
