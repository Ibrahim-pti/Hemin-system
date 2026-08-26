@extends('layouts.app')
@section('title', 'مەوادی خاو و کۆگا')

@section('actions')
    <div class="flex items-center gap-2">
        <button type="button" @click="$dispatch('open-stock-in-modal')" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-all cursor-pointer">
            📥 هاتنی مەواد
        </button>
        <button type="button" @click="$dispatch('open-stock-out-modal')" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-all cursor-pointer">
            📤 بەکارهێنانی مەواد
        </button>
        <button type="button" @click="$dispatch('open-new-material-modal')" class="btn btn-primary !py-1.5 !px-3.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 shadow-xs cursor-pointer">
            + مەوادی نوێ
        </button>
    </div>
@endsection

@section('content')
<div x-data="{
    showNewMaterialModal: false,
    showStockInModal: false,
    showStockOutModal: false,
    init() {
        window.addEventListener('open-new-material-modal', () => this.showNewMaterialModal = true);
        window.addEventListener('open-stock-in-modal', () => this.showStockInModal = true);
        window.addEventListener('open-stock-out-modal', () => this.showStockOutModal = true);
    }
}" class="space-y-6">

    {{-- ١. سەردێڕی مەوادی خاو --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-2xl shadow-xs">
                📦
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800">مەوادی خاو و کەرەستەی دروستکردن</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    کەرەستەی بەردەست لە شوێنی دروستکردن، زیادکردن، بەکارهێنان و ئاگاداری کەمبوونەوە
                </p>
            </div>
        </div>
    </div>

    {{-- ٢. ئاگاداری مەوادە کەمبووەکان --}}
    @if ($lowStockMaterials->isNotEmpty())
        <div class="bg-gradient-to-r from-rose-50 to-amber-50 rounded-2xl p-4 border border-rose-200 shadow-xs">
            <div class="flex items-center gap-2.5 mb-2.5">
                <span class="flex size-7 items-center justify-center rounded-lg bg-rose-600 text-white font-bold text-xs shadow-xs">⚠️</span>
                <h3 class="font-black text-xs text-rose-900">مەوادە کەمبووەکانی کارگە ({{ $lowStockMaterials->count() }} جۆر)</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                @foreach ($lowStockMaterials as $lowMat)
                    <div class="bg-white rounded-xl p-2.5 border border-rose-200 flex items-center justify-between gap-2 shadow-2xs">
                        <div class="min-w-0">
                            <div class="font-bold text-xs text-slate-800 truncate">{{ $lowMat->name }}</div>
                            <div class="text-[11px] font-black text-rose-600">ماوە: {{ fmt_num($lowMat->stock_qty) }} {{ $lowMat->unit?->name }}</div>
                        </div>
                        <button type="button" @click="showStockInModal = true" class="btn btn-ghost !py-1 !px-2 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 cursor-pointer">
                            + زیادکردن
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ٣. خشتەی سەرەکی مەوادەکان --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs font-bold text-slate-600">
                کۆی گشتی: <span class="text-slate-900 font-black">{{ $rawMaterials->total() }}</span> جۆر مەواد
            </div>
            <form method="GET" action="{{ route('workshop.materials') }}" class="flex items-center gap-2">
                <input type="text" name="mat_q" value="{{ request('mat_q') }}" placeholder="گەڕان بە ناوی مەواد یان کۆد..."
                       class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 w-60 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <button type="submit" class="btn btn-primary !py-1.5 !px-3 text-xs">گەڕان</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-3.5 font-bold">کۆد</th>
                        <th class="p-3.5 font-bold">ناوی مەواد</th>
                        <th class="p-3.5 font-bold">جۆر (بەش)</th>
                        <th class="p-3.5 font-bold text-center">بڕی بەردەست</th>
                        <th class="p-3.5 font-bold text-center">کەمترین بڕ</th>
                        <th class="p-3.5 font-bold text-center">دۆخ</th>
                        <th class="p-3.5 font-bold text-center">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rawMaterials as $mat)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 font-mono text-slate-500 font-medium">{{ $mat->code }}</td>
                            <td class="p-3.5 font-bold text-slate-900">{{ $mat->name }}</td>
                            <td class="p-3.5 text-slate-500">{{ $mat->category?->name ?? '—' }}</td>
                            <td class="p-3.5 text-center font-black text-sm num {{ $mat->is_low ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ fmt_num($mat->stock_qty) }} <span class="text-xs font-normal text-slate-500">{{ $mat->unit?->name }}</span>
                            </td>
                            <td class="p-3.5 text-center font-medium text-slate-500 num">
                                {{ fmt_num($mat->min_qty) }} {{ $mat->unit?->name }}
                            </td>
                            <td class="p-3.5 text-center">
                                @if ($mat->is_low)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 animate-pulse">
                                        کەمە ⚠️
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        بەردەستە ✔️
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="showStockInModal = true"
                                            class="px-2 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 cursor-pointer">
                                        + هاتن
                                    </button>
                                    <button type="button" @click="showStockOutModal = true"
                                            class="px-2 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 cursor-pointer">
                                        - بەکارهێنان
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-xs font-bold">هیچ مەوادێک لەم کۆگایەدا نییە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-100">
            {{ $rawMaterials->links() }}
        </div>
    </div>

    {{-- مۆداڵی زیادکردنی مەوادی نوێ --}}
    <div x-show="showNewMaterialModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showNewMaterialModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">📦</span>
                    <span>زیادکردنی مەوادی خاوی نوێ</span>
                </div>
                <button type="button" @click="showNewMaterialModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.store-material') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ناوی مەواد <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="ناوی مەواد بنووسە..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">جۆر / بەش</label>
                        <select name="item_category_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                            <option value="">دیارینەکراوە</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">یەکە <span class="text-rose-500">*</span></label>
                        <select name="unit_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">بڕی سەرەتایی</label>
                        <input type="number" step="any" name="initial_qty" value="0"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">کەمترین بڕ</label>
                        <input type="number" step="any" name="min_qty" value="5"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" name="note" placeholder="تێبینی..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showNewMaterialModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-blue-600 hover:bg-blue-700">تۆمارکردن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی زیادکردنی بڕ بۆ مەواد (Stock In) --}}
    <div x-show="showStockInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockInModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">📥</span>
                    <span>هاتنی مەواد بۆ کارگە</span>
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
                    <input type="text" name="note" placeholder="سەرچاوە یان تێبینی..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showStockInModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700">تۆمارکردنی هاتن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی بەکارهێنانی مەواد (Stock Out) --}}
    <div x-show="showStockOutModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockOutModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">📤</span>
                    <span>بەکارهێنان و کەمکردنەوەی مەواد</span>
                </div>
                <button type="button" @click="showStockOutModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-out') }}" class="space-y-3.5">
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">بڕی بەکارهاتوو <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="qty" required placeholder="0.00"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono text-base font-bold text-amber-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" name="note" placeholder="بۆ چی بەکارهات..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showStockOutModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-amber-600 hover:bg-amber-700">تۆمارکردنی بەکارهێنان</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
