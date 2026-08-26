<!DOCTYPE html>
<html lang="ckb" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>چوونەژوورەوە — {{ \App\Models\Setting::get('company_name', 'کارگەی ئاسنگەری هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f1f5f9;
            background-image:
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(20, 184, 166, 0.06) 0px, transparent 50%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center p-4 sm:p-6 antialiased font-sans text-slate-800"
      x-data="{
          selectedRole: 'admin',
          email: 'admin@hemin.krd',
          password: '',
          setRole(role) {
              this.selectedRole = role;
              if (role === 'admin') {
                  this.email = 'admin@hemin.krd';
              } else {
                  this.email = 'kogha@hemin.krd';
              }
              this.password = '';
              this.$nextTick(() => {
                  document.getElementById('password').focus();
              });
          }
      }">

    <div class="w-full max-w-md">

        {{-- کارتی سەرەکی لۆگین --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/50 p-6 sm:p-8">

            {{-- لۆگۆ و ناوی کارگە --}}
            <div class="text-center mb-7">
                <div class="inline-flex size-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-2xl shadow-lg shadow-blue-500/25 mb-3 border border-blue-400/30">
                    هـ
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">
                    {{ \App\Models\Setting::get('company_name', 'کارگەی ئاسنگەری هێمن') }}
                </h1>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    سیستەمی بەڕێوەبردنی فرۆشتن، کۆگا و کارگە
                </p>
            </div>

            {{-- هەڵبژاردنی بەش / ڕۆڵ (دوو داشبۆردەکە) --}}
            <div class="mb-6">
                <div class="text-xs font-bold text-slate-700 mb-2.5 text-right flex items-center justify-between">
                    <span>دیاریکردنی بەشی چوونەژوورەوە:</span>
                    <span class="text-[11px] text-blue-600 font-semibold" x-text="selectedRole === 'admin' ? 'ئۆفیسی بەڕێوەبردن' : 'بەشی کارگە'"></span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- بەڕێوەبەر --}}
                    <button type="button" @click="setRole('admin')"
                            :class="selectedRole === 'admin' ? 'bg-blue-50/90 border-blue-500 ring-2 ring-blue-200 text-blue-900 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100/80 hover:border-slate-300'"
                            class="relative p-3.5 rounded-2xl border transition-all text-right flex flex-col justify-between cursor-pointer group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="size-9 rounded-xl flex items-center justify-center text-lg transition-transform group-hover:scale-110"
                                 :class="selectedRole === 'admin' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700'">
                                🏢
                            </div>
                            <span x-show="selectedRole === 'admin'" class="size-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-bold">✓</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold" :class="selectedRole === 'admin' ? 'text-blue-900' : 'text-slate-800'">بەڕێوەبەر</div>
                            <div class="text-[10px] text-slate-500 mt-0.5 font-medium">ئۆفیس و فرۆشتن</div>
                        </div>
                    </button>

                    {{-- وەستا --}}
                    <button type="button" @click="setRole('wasta')"
                            :class="selectedRole === 'wasta' ? 'bg-indigo-50/90 border-indigo-500 ring-2 ring-indigo-200 text-indigo-900 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100/80 hover:border-slate-300'"
                            class="relative p-3.5 rounded-2xl border transition-all text-right flex flex-col justify-between cursor-pointer group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="size-9 rounded-xl flex items-center justify-center text-lg transition-transform group-hover:scale-110"
                                 :class="selectedRole === 'wasta' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700'">
                                ⚒️
                            </div>
                            <span x-show="selectedRole === 'wasta'" class="size-5 rounded-full bg-indigo-600 text-white text-[11px] flex items-center justify-center font-bold">✓</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold" :class="selectedRole === 'wasta' ? 'text-indigo-900' : 'text-slate-800'">وەستا</div>
                            <div class="text-[10px] text-slate-500 mt-0.5 font-medium">کارگە و دروستکردن</div>
                        </div>
                    </button>
                </div>
            </div>

            {{-- فۆرمی چوونەژوورەوە --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                @if (isset($errors) && $errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700 font-semibold flex items-center gap-2">
                        <svg class="size-4.5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="email">ئیمەیڵ</label>
                    <div class="relative">
                        <input id="email" name="email" type="email" required
                               x-model="email"
                               class="w-full bg-slate-50/60 border border-slate-300 rounded-xl py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-medium transition-all"
                               dir="ltr" placeholder="admin@hemin.krd">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="password">وشەی نهێنی</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autofocus
                               x-model="password"
                               class="w-full bg-slate-50/60 border border-slate-300 rounded-xl py-2.5 px-3.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-medium transition-all"
                               dir="ltr" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none font-medium">
                        <input type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>لەبیرم مەکە</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white shadow-md transition-all active:scale-98 cursor-pointer mt-2"
                        :class="selectedRole === 'admin' ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/20'">
                    <span x-text="selectedRole === 'admin' ? 'چوونەژوورەوە بۆ ئۆفیسی فرۆشتن 🏢' : 'چوونەژوورەوە بۆ کارگەی دروستکردن ⚒️'"></span>
                </button>
            </form>
        </div>

        {{-- ناونیشانی خوارەوە --}}
        <p class="mt-5 text-center text-xs text-slate-500 font-medium">
            {{ \App\Models\Setting::get('company_address', 'هەولێر — کۆمەڵگەی پیشەسازی') }}
        </p>
    </div>

</body>
</html>
