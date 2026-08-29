@extends('layouts.menu')
@section('title', 'مەخزەن')

@section('content')
<div x-data="workshopMaterialsApp()" class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: سەردێڕ و دوگمە سەرەکییەکان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-11 sm:size-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-xl sm:text-2xl shadow-xs shrink-0">
                📦
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-slate-800">مەخزەن و کەرەستەی دروستکردن</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    کەرەستەی بەردەست لە شوێنی دروستکردن ({{ $workshopWarehouse?->name ?? 'کۆگای کارگە' }})
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <button type="button" @click="showStockInModal = true" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 cursor-pointer flex-1 sm:flex-initial text-center justify-center">
                📥 هاتنی مەواد
            </button>
            <button type="button" @click="showStockOutModal = true" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 cursor-pointer flex-1 sm:flex-initial text-center justify-center">
                📤 بەکارهێنان
            </button>
            <button type="button" @click="showNewMaterialModal = true" class="btn btn-primary !py-1.5 !px-3.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 cursor-pointer shadow-xs w-full sm:w-auto text-center justify-center">
                + مەوادی نوێ
            </button>
        </div>
    </div>

    {{-- ٢. ئاگاداری مەوادە کەمبووەکان --}}
    @if ($lowStockMaterials->isNotEmpty())
        <div class="bg-rose-50 rounded-2xl p-3.5 sm:p-4 border border-rose-200 shadow-xs">
            <div class="flex items-center gap-2 mb-2">
                <span class="size-6 sm:size-7 rounded-lg bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">⚠️</span>
                <h3 class="font-black text-xs sm:text-sm text-rose-900">
                    مەوادە کەمبووەکان ({{ $lowStockMaterials->count() }} جۆر کەمترە لە کەمترین بڕ)
                </h3>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($lowStockMaterials as $lm)
                    <div class="bg-white px-2.5 sm:px-3 py-1.5 rounded-xl border border-rose-200 text-xs flex items-center gap-2 shadow-2xs">
                        <span class="font-black text-slate-800">{{ $lm->name }}:</span>
                        <span class="font-bold text-rose-600">{{ fmt_num($lm->stock_qty) }} {{ $lm->unit?->name }}</span>
                        <span class="text-slate-400 text-[10px]">(کەمترین: {{ fmt_num($lm->min_qty) }})</span>
                        <button type="button" @click="openStockInModalFor('{{ $lm->id }}')" class="text-blue-600 hover:underline font-bold text-[11px] mr-1 cursor-pointer">
                            + هاتن
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ٣. بەشی سەرەکی مەوادەکان --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 sm:p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-xs font-bold text-slate-600">
                کۆی گشتی: <span class="text-slate-900 font-black" x-text="materialsList.length"></span> جۆر مەواد
            </div>
            <div class="w-full sm:w-auto">
                <input type="text" x-model="materialSearch" placeholder="گەڕانی خێرا بە ناوی مەواد یان کۆد..."
                       class="text-xs px-3 py-2 rounded-xl border border-slate-200 w-full sm:w-64 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50 sm:bg-white">
            </div>
        </div>

        {{-- ١. پێشاندانی کارتی مۆبایل (بۆ شاشەی بچووک بێ سکرۆڵی ئاسۆیی) --}}
        <div class="block md:hidden divide-y divide-slate-100">
            <template x-for="mat in filteredMaterials" :key="mat.id">
                <div class="p-3.5 space-y-2.5 hover:bg-slate-50/80 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-black text-sm text-slate-900" x-text="mat.name"></div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                <span x-text="mat.code"></span>
                                <span x-show="mat.category_name" class="text-slate-300 mx-1">•</span>
                                <span x-show="mat.category_name" class="text-slate-500 font-sans" x-text="mat.category_name"></span>
                            </div>
                        </div>
                        <div>
                            <span x-show="mat.is_low" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                کەمە ⚠️
                            </span>
                            <span x-show="!mat.is_low" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                بەردەستە ✔️
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-slate-500 text-[11px]">بڕی بەردەست:</span>
                            <span class="font-black text-sm num mr-1" :class="mat.is_low ? 'text-rose-600' : 'text-slate-800'">
                                <span x-text="mat.stock_qty"></span> <span class="text-xs font-normal text-slate-500" x-text="mat.unit_name"></span>
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px]">کەمترین:</span>
                            <span class="font-bold text-slate-600 num mr-1" x-text="mat.min_qty + ' ' + (mat.unit_name || '')"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" @click="openStockInModalFor(mat.id)"
                                class="flex-1 py-1.5 px-3 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 flex items-center justify-center gap-1 cursor-pointer">
                            <span>📥</span><span>+ هاتن</span>
                        </button>
                        <button type="button" @click="openStockOutModalFor(mat.id)"
                                class="flex-1 py-1.5 px-3 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 flex items-center justify-center gap-1 cursor-pointer">
                            <span>📤</span><span>- بەکارهێنان</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- ٢. خشتەی دیسکتۆپ و تابلێت (شاشەی گەورە) --}}
        <div class="hidden md:block overflow-x-auto scrollbar-none">
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
                    <template x-for="mat in filteredMaterials" :key="mat.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 font-mono text-slate-500 font-medium" x-text="mat.code"></td>
                            <td class="p-3.5 font-bold text-slate-900" x-text="mat.name"></td>
                            <td class="p-3.5 text-slate-500" x-text="mat.category_name || '—'"></td>
                            <td class="p-3.5 text-center font-black text-sm num"
                                :class="mat.is_low ? 'text-rose-600' : 'text-slate-800'">
                                <span x-text="mat.stock_qty"></span> <span class="text-xs font-normal text-slate-500" x-text="mat.unit_name"></span>
                            </td>
                            <td class="p-3.5 text-center font-medium text-slate-500 num" x-text="mat.min_qty + ' ' + (mat.unit_name || '')"></td>
                            <td class="p-3.5 text-center">
                                <span x-show="mat.is_low" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    کەمە ⚠️
                                </span>
                                <span x-show="!mat.is_low" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    بەردەستە ✔️
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="openStockInModalFor(mat.id)"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 cursor-pointer">
                                        + هاتن
                                    </button>
                                    <button type="button" @click="openStockOutModalFor(mat.id)"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 cursor-pointer">
                                        - بەکارهێنان
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- مۆداڵەکان (MODALS) --}}
    {{-- ============================================================ --}}

    {{-- مۆداڵی زیادکردنی مەوادی نوێ --}}
    <div x-show="showNewMaterialModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-3.5 sm:p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-5 sm:p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto scrollbar-none" @click.outside="showNewMaterialModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
                <div class="flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">📦</span>
                    <h3 class="font-black text-slate-800 text-sm sm:text-base">زیادکردنی مەوادی نوێ بۆ مەخزەن</h3>
                </div>
                <button type="button" @click="showNewMaterialModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.store-material') }}" class="space-y-3.5"
                  x-data="{
                      catMode: 'select',
                      unitMode: 'select',
                      selectedCat: '',
                      selectedUnit: '{{ $units->first()?->id ?? '' }}',
                      newCatName: '',
                      newUnitName: '',
                  }">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ناوی مەواد <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="وەک: بۆری ئاسن، ئەلەمنیۆم، تەختە..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- جۆر / پۆل --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">جۆر / پۆل</label>
                            <button type="button" 
                                    x-show="catMode === 'select'" 
                                    @click="catMode = 'new'; selectedCat = ''; $nextTick(() => $refs.newCatInput?.focus())"
                                    class="text-[11px] font-bold text-blue-600 hover:underline cursor-pointer">
                                + نوێ
                            </button>
                            <button type="button" 
                                    x-show="catMode === 'new'" 
                                    @click="catMode = 'select'; newCatName = ''"
                                    class="text-[11px] font-bold text-slate-500 hover:underline cursor-pointer">
                                هەڵبژاردن لە لیست
                            </button>
                        </div>

                        <div x-show="catMode === 'select'">
                            <select name="item_category_id" 
                                    x-model="selectedCat"
                                    @change="if($event.target.value === '__NEW__') { catMode = 'new'; selectedCat = ''; $nextTick(() => $refs.newCatInput?.focus()); }"
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white">
                                <option value="">هەڵبژێرە...</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                                <option value="__NEW__" class="font-black text-blue-600 bg-blue-50">➕ + نووسینی پۆلی نوێ...</option>
                            </select>
                        </div>

                        <div x-show="catMode === 'new'" x-cloak class="relative">
                            <input type="text" 
                                   name="new_category_name" 
                                   x-ref="newCatInput"
                                   x-model="newCatName" 
                                   placeholder="ناوی پۆل بنووسە..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-blue-400 bg-blue-50/30 focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-500 font-medium">
                        </div>
                    </div>

                    {{-- یەکە --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">یەکە <span class="text-rose-500">*</span></label>
                            <button type="button" 
                                    x-show="unitMode === 'select'" 
                                    @click="unitMode = 'new'; selectedUnit = ''; $nextTick(() => $refs.newUnitInput?.focus())"
                                    class="text-[11px] font-bold text-blue-600 hover:underline cursor-pointer">
                                + نوێ
                            </button>
                            <button type="button" 
                                    x-show="unitMode === 'new'" 
                                    @click="unitMode = 'select'; newUnitName = ''; selectedUnit = '{{ $units->first()?->id ?? '' }}'"
                                    class="text-[11px] font-bold text-slate-500 hover:underline cursor-pointer">
                                هەڵبژاردن لە لیست
                            </button>
                        </div>

                        <div x-show="unitMode === 'select'">
                            <select name="unit_id" 
                                    x-model="selectedUnit"
                                    :required="unitMode === 'select'"
                                    @change="if($event.target.value === '__NEW__') { unitMode = 'new'; selectedUnit = ''; $nextTick(() => $refs.newUnitInput?.focus()); }"
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white">
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                                <option value="__NEW__" class="font-black text-blue-600 bg-blue-50">➕ + نووسینی یەکەی نوێ...</option>
                            </select>
                        </div>

                        <div x-show="unitMode === 'new'" x-cloak class="relative">
                            <input type="text" 
                                   name="new_unit_name" 
                                   x-ref="newUnitInput"
                                   x-model="newUnitName" 
                                   :required="unitMode === 'new'"
                                   placeholder="ناوی یەکە بنووسە..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-blue-400 bg-blue-50/30 focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-500 font-medium">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">بڕی سەرەتایی</label>
                        <input type="number" step="any" name="initial_qty" value="0"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">کەمترین بڕ (ئاگاداری)</label>
                        <input type="number" step="any" name="min_qty" value="5"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <textarea name="note" rows="2" placeholder="قیاس، کوالێتی..."
                              class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showNewMaterialModal = false" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold">داخستن</button>
                    <button type="submit" class="btn btn-primary !py-1.5 !px-4 text-xs font-bold bg-blue-600 hover:bg-blue-700">زیادکردن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی هاتنی مەواد (Stock In) --}}
    <div x-show="showStockInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-3.5 sm:p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-5 sm:p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto scrollbar-none" @click.outside="showStockInModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
                <div class="flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0">📥</span>
                    <h3 class="font-black text-slate-800 text-sm sm:text-base">هاتنی مەواد بۆ کارگە (+ بڕ)</h3>
                </div>
                <button type="button" @click="showStockInModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-in') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مەواد *</label>
                    <select name="item_id" x-model="selectedItemId" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                        <option value="">مەوادەکە دیاری بکە...</option>
                        @foreach ($rawMaterials as $mat)
                            <option value="{{ $mat->id }}">{{ $mat->name }} (ماوە: {{ fmt_num($mat->stock_qty) }} {{ $mat->unit?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بڕی هاتوو *</label>
                    <input type="number" step="any" min="0.01" name="qty" required placeholder="چەند دانە یان مەتر..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی یان هۆکار</label>
                    <input type="text" name="note" placeholder="وەک: هات لە مەخزەنی سەرەکی..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showStockInModal = false" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold">داخستن</button>
                    <button type="submit" class="btn btn-primary !py-1.5 !px-4 text-xs font-bold bg-emerald-600 hover:bg-emerald-700">تۆمارکردنی هاتن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی بەکارهێنانی مەواد (Stock Out) --}}
    <div x-show="showStockOutModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-3.5 sm:p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-5 sm:p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto scrollbar-none" @click.outside="showStockOutModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
                <div class="flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm shrink-0">📤</span>
                    <h3 class="font-black text-slate-800 text-sm sm:text-base">بەکارهێنان و کەمکردنەوەی مەواد (- بڕ)</h3>
                </div>
                <button type="button" @click="showStockOutModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-out') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">مەواد *</label>
                    <select name="item_id" x-model="selectedItemId" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                        <option value="">مەوادەکە دیاری بکە...</option>
                        @foreach ($rawMaterials as $mat)
                            <option value="{{ $mat->id }}">{{ $mat->name }} (بەردەست: {{ fmt_num($mat->stock_qty) }} {{ $mat->unit?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بڕی بەکارهاتوو *</label>
                    <input type="number" step="any" min="0.01" name="qty" required placeholder="بڕی سەرفکراو..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی (بۆچی بەکارهات)</label>
                    <input type="text" name="note" placeholder="وەک: بۆ وەسڵی #12..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showStockOutModal = false" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold">داخستن</button>
                    <button type="submit" class="btn btn-primary !py-1.5 !px-4 text-xs font-bold bg-amber-600 hover:bg-amber-700">تۆمارکردنی بەکارهێنان</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function workshopMaterialsApp() {
    return {
        materialSearch: '',
        showNewMaterialModal: false,
        showStockInModal: false,
        showStockOutModal: false,
        selectedItemId: '',
        materialsList: @json($materialsData),

        get filteredMaterials() {
            const q = this.materialSearch.trim().toLowerCase();
            if (!q) return this.materialsList;
            return this.materialsList.filter(m => 
                (m.name && m.name.toLowerCase().includes(q)) || 
                (m.code && m.code.toLowerCase().includes(q))
            );
        },

        openStockInModalFor(itemId) {
            this.selectedItemId = itemId;
            this.showStockInModal = true;
        },

        openStockOutModalFor(itemId) {
            this.selectedItemId = itemId;
            this.showStockOutModal = true;
        }
    };
}
</script>
@endsection

