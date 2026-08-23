@extends('layouts.app')
@section('title', $item->exists ? 'دەستکاری مەواد' : 'مەوادی نوێ')

@section('content')
<div style="width: 100%; display: flex; flex-direction: column; gap: 1.25rem;">

    {{-- ١. سەردێڕی سەرەوە --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">
                    {{ $item->exists ? 'دەستکاری مەواد' : 'مەوادی نوێ' }}
                </h1>
                <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin: 0.15rem 0 0 0;">
                    {{ $item->exists ? 'دەستکاریکردنی زانیارییەکانی ئەم مەوادە' : 'تۆمارکردن و زیادکردنی مەوادی نوێ بۆ کۆگا' }}
                </p>
            </div>
        </div>

        <a href="{{ route('items.index') }}"
           style="background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 0.55rem 1.25rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <span>&larr;</span>
            <span>گەڕانەوە بۆ کۆگا</span>
        </a>
    </div>

    {{-- ٢. فۆڕمی سەرەکی بە پانتایی تەواوی شاشە --}}
    <form method="POST"
          action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}"
          enctype="multipart/form-data"
          x-data="{
              selectedUnit: '{{ old('unit_id', $item->unit_id) }}',
              imagePreview: '{{ $item->imageUrl() }}',
              unitNames: {
                  @foreach ($units as $unit)
                      '{{ $unit->id }}': '{{ $unit->name }}',
                  @endforeach
              },
              get unitText() {
                  return this.unitNames[this.selectedUnit] || '';
              },
              onImageChange(e) {
                  const file = e.target.files[0];
                  if (file) {
                      this.imagePreview = URL.createObjectURL(file);
                  }
              }
          }"
          style="width: 100%;">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden; width: 100%;">

            {{-- سەردێڕی ناوەوەی کارت --}}
            <div style="padding: 1.25rem 1.75rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.6rem; font-weight: 800; font-size: 1rem; color: #1e293b;">
                    <span style="color: #2563eb;">📦</span>
                    <span>{{ $item->exists ? 'دەستکاریکردنی زانیارییەکانی مەواد' : 'تۆمارکردنی مەوادی نوێ لە کۆگا' }}</span>
                </div>
                <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
                    ناو، یەکە، بڕ و تێچووی کڕین دیاری بکە
                </span>
            </div>

            <div style="padding: 2rem 2.25rem; display: flex; flex-direction: column; gap: 1.5rem;">

                @if (isset($errors) && $errors->any())
                    <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 0.85rem; padding: 1rem 1.25rem; color: #b91c1c; font-size: 0.85rem;">
                        <div style="font-weight: 800; margin-bottom: 0.35rem;">تکایە ئەم هەڵانە چاک بکە:</div>
                        <ul style="margin: 0; padding-right: 1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ڕیزی ١: ناو و وێنەی مەواد --}}
                <div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 1.5rem; align-items: start;">
                    {{-- ناوی مەواد --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem; display: block;" for="name">
                            ناوی مەواد <span style="color: #ef4444;">*</span>
                        </label>
                        <input id="name" name="name" class="field" required
                               value="{{ old('name', $item->name) }}"
                               placeholder="ناوی مەواد لێرە بنووسە..."
                               style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 600;">
                        @error('name') <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>

                    {{-- وێنەی مەواد --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem; display: block;">
                            وێنەی مەواد <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">(ئارەزوومەندانە)</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <label style="width: 3.25rem; height: 3.25rem; border-radius: 0.75rem; border: 2px dashed #cbd5e1; background: #f8fafc; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #94a3b8; overflow: hidden; flex-shrink: 0; transition: border-color 0.2s;"
                                   onmouseover="this.style.borderColor='#2563eb'"
                                   onmouseout="this.style.borderColor='#cbd5e1'">
                                <input type="file" name="image" accept="image/*" style="display: none;" @change="onImageChange($event)">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" style="width: 100%; height: 100%; object-fit: cover;">
                                </template>
                                <template x-if="!imagePreview">
                                    <svg style="width: 1.5rem; height: 1.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </template>
                            </label>
                            <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">دانانی وێنەی کاڵا</span>
                        </div>
                        @error('image') <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- ڕیزی ٢: یەکە، بڕ، تێچووی کڕین و بەرواری کڕین (٤ ستوون) --}}
                <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.25rem;">
                    {{-- یەکە --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem; display: block;" for="unit_id">
                            یەکە <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="unit_id" name="unit_id" x-model="selectedUnit" class="field" required style="width: 100%; padding: 0.75rem 1rem; font-weight: 600;">
                            <option value="">— هەڵبژێرە (دانە، تەن...) —</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id) == $unit->id)>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id') <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>

                    {{-- بڕ --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem; display: block;" for="min_qty">
                            بڕ
                        </label>
                        <div style="position: relative;">
                            <input id="min_qty" name="min_qty" type="number" step="any" min="0"
                                   class="field num"
                                   value="{{ old('min_qty', ($item->exists && (float)$item->min_qty > 0) ? (float)$item->min_qty : '') }}"
                                   placeholder="0"
                                   style="width: 100%; padding: 0.75rem 1rem; text-align: center; font-weight: 700; font-size: 1.1rem;">
                            <div style="position: absolute; top: 50%; left: 0.75rem; transform: translateY(-50%); background: #f1f5f9; color: #475569; padding: 0.15rem 0.5rem; border-radius: 0.35rem; font-size: 0.75rem; font-weight: 700; pointer-events: none;"
                                 x-show="unitText" x-text="unitText"></div>
                        </div>
                        @error('min_qty') <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>

                    {{-- تێچووی کڕین (بە دینار) --}}
                    @can('view_reports')
                        <div x-data="{
                            formatInput(e) {
                                let clean = e.target.value.replace(/[^0-9.]/g, '');
                                let parts = clean.split('.');
                                if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                                let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
                                let dec = parts.length > 1 ? '.' + parts[1] : '';
                                e.target.value = int ? int + dec : '';
                            }
                        }">
                            <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem; display: block;" for="last_cost">
                                تێچووی کڕین
                            </label>
                            <div style="position: relative;">
                                <input id="last_cost" name="last_cost" type="text" inputmode="numeric"
                                       value="{{ old('last_cost', ($item->exists && (float)$item->last_cost > 0) ? number_format((float)$item->last_cost, 0, '.', ',') : '') }}"
                                       @input="formatInput($event)"
                                       class="field num"
                                       placeholder="0"
                                       style="width: 100%; padding: 0.75rem 1rem; text-align: center; font-weight: 700; font-size: 1.1rem;">
                                <span style="position: absolute; top: 50%; left: 0.75rem; transform: translateY(-50%); font-size: 0.75rem; font-weight: 700; color: #94a3b8; pointer-events: none;">
                                    د.ع
                                </span>
                            </div>
                            @error('last_cost') <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                            <input type="hidden" name="cost_currency" value="IQD">
                        </div>
                    @endcan

                    {{-- بەرواری کڕین --}}
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem; display: block;" for="purchase_date">
                            بەرواری کڕین <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">(ئارەزوومەندانە)</span>
                        </label>
                        <input id="purchase_date" name="purchase_date" type="date" class="field num"
                               value="{{ old('purchase_date', $item->purchase_date ? $item->purchase_date->format('Y-m-d') : '') }}"
                               style="width: 100%; padding: 0.75rem 1rem; font-weight: 600;">
                        @error('purchase_date') <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- ڕیزی ٣: تێبینی --}}
                <div>
                    <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem; display: block;" for="note">
                        تێبینی <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">(ئارەزوومەندانە)</span>
                    </label>
                    <textarea id="note" name="note" rows="3" class="field"
                              placeholder="ڕوونکردنەوە، قیاس، یان تێبینی تایبەت لەسەر ئەم مەوادە..."
                              style="width: 100%; padding: 0.75rem 1rem; font-size: 0.9rem;">{{ old('note', $item->note) }}</textarea>
                </div>

                <input type="hidden" name="is_active" value="1">
                <input type="hidden" name="is_for_sale" value="0">

            </div>

            {{-- دوگمەکانی خوارەوە --}}
            <div style="background: #f8fafc; padding: 1.25rem 2.25rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <button type="submit"
                            style="background: #2563eb; color: #ffffff; padding: 0.65rem 2rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                        <span>✓</span>
                        <span>{{ $item->exists ? 'نوێکردنەوەی مەواد' : 'زیادکردنی مەواد' }}</span>
                    </button>
                    <a href="{{ route('items.index') }}"
                       style="padding: 0.65rem 1.5rem; border-radius: 0.65rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.9rem; text-decoration: none;">
                        پاشگەزبوونەوە
                    </a>
                </div>

                @if ($item->exists)
                    <button type="submit" form="delete-item"
                            style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; padding: 0.65rem 1.25rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; cursor: pointer;"
                            onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم مەوادە؟')">
                        سڕینەوەی مەواد
                    </button>
                @endif
            </div>

        </div>
    </form>

    @if ($item->exists)
        <form id="delete-item" method="POST" action="{{ route('items.destroy', $item) }}" style="display: none;">
            @csrf @method('DELETE')
        </form>
    @endif

</div>
@endsection
