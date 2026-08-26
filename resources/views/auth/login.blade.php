<!DOCTYPE html>
<html lang="ckb" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>چوونەژوورەوە — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: radial-gradient(circle at top right, #1e293b, #0f172a 60%, #020617);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center p-4 antialiased text-slate-100"
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

        {{-- لۆگۆ و ناوی کارگە --}}
        <div class="mb-8 text-center">
            <div class="mx-auto mb-3.5 flex size-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-2xl font-black text-white shadow-xl shadow-blue-500/25 border border-blue-400/30">
                هـ
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">{{ \App\Models\Setting::get('company_name', 'کارگەی ئاسنگەری هێمن') }}</h1>
            <p class="mt-1 text-xs text-slate-400 font-medium">
                سیستەمی بەڕێوەبردنی فرۆشتن، کۆگا و کارگەی دروستکردن
            </p>
        </div>

        {{-- کارتی سەرەکی چوونەژوورەوە --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl">

            {{-- هەڵبژاردنی ڕۆڵ (دوو داشبۆردەکە) --}}
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-400 mb-2.5 text-center">
                    تکایە بەشی مەبەست دیاری بکە بۆ چوونەژوورەوە:
                </label>
                <div class="grid grid-cols-2 gap-2.5 p-1.5 bg-slate-950/80 rounded-2xl border border-slate-800/80">
                    {{-- ڕۆڵی بەڕێوەبەر --}}
                    <button type="button" @click="setRole('admin')"
                            :class="selectedRole === 'admin' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 border-blue-500' : 'text-slate-400 hover:text-slate-200 border-transparent'"
                            class="flex flex-col items-center justify-center p-3 rounded-xl border transition-all text-center group cursor-pointer">
                        <span class="text-2xl mb-1 group-hover:scale-110 transition-transform">🏢</span>
                        <span class="text-xs font-bold">بەڕێوەبەر</span>
                        <span class="text-[10px] opacity-75 mt-0.5">ئۆفیس و فرۆشتن</span>
                    </button>

                    {{-- ڕۆڵی وەستا --}}
                    <button type="button" @click="setRole('wasta')"
                            :class="selectedRole === 'wasta' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 border-indigo-500' : 'text-slate-400 hover:text-slate-200 border-transparent'"
                            class="flex flex-col items-center justify-center p-3 rounded-xl border transition-all text-center group cursor-pointer">
                        <span class="text-2xl mb-1 group-hover:scale-110 transition-transform">⚒️</span>
                        <span class="text-xs font-bold">وەستا</span>
                        <span class="text-[10px] opacity-75 mt-0.5">کارگە و دروستکردن</span>
                    </button>
                </div>
            </div>

            {{-- فۆرمی تێپەڕەوشە و ئیمەیڵ --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                @if (isset($errors) && $errors->any())
                    <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs text-rose-400 font-semibold flex items-center gap-2">
                        <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5" for="email">ئیمەیڵ</label>
                    <input id="email" name="email" type="email" required
                           x-model="email"
                           class="w-full bg-slate-950/60 border border-slate-700/80 rounded-xl py-2.5 px-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-medium"
                           dir="ltr" placeholder="admin@hemin.krd">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5" for="password">وشەی نهێنی</label>
                    <input id="password" name="password" type="password" required autofocus
                           x-model="password"
                           class="w-full bg-slate-950/60 border border-slate-700/80 rounded-xl py-2.5 px-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-medium"
                           dir="ltr" placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="size-4 rounded bg-slate-950 border-slate-700 text-blue-600 focus:ring-0">
                        <span>لەبیرم مەکە</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white shadow-lg transition-all active:scale-98 cursor-pointer mt-2"
                        :class="selectedRole === 'admin' ? 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 shadow-blue-600/30' : 'bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 shadow-indigo-600/30'">
                    <span x-text="selectedRole === 'admin' ? 'چوونەژوورەوە بۆ ئۆفیسی فرۆشتن 🏢' : 'چوونەژوورەوە بۆ کارگەی دروستکردن ⚒️'"></span>
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            {{ \App\Models\Setting::get('company_address', 'هەولێر — کۆمەڵگەی پیشەسازی') }}
        </p>
    </div>

</body>
</html>
