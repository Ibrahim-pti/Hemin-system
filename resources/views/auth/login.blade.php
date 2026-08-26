<!DOCTYPE html>
<html lang="ckb" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>چوونەژوورەوە — {{ \App\Models\Setting::get('company_name', 'کارگەی ئاسنگەری هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-bg-pattern {
            background-color: #0f172a;
            background-image: radial-gradient(at 100% 0%, rgba(59, 130, 246, 0.18) 0px, transparent 50%),
                              radial-gradient(at 0% 100%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                              radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.9) 0px, transparent 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="h-full bg-slate-50 antialiased font-sans text-slate-800"
      x-data="{
          selectedRole: 'admin',
          email: 'admin@hemin.krd',
          password: '',
          showPassword: false,
          setRole(role) {
              this.selectedRole = role;
              if (role === 'admin') {
                  this.email = 'admin@hemin.krd';
              } else {
                  this.email = 'kogha@hemin.krd';
              }
              this.password = '';
              this.$nextTick(() => {
                  const passInput = document.getElementById('password');
                  if (passInput) passInput.focus();
              });
          }
      }">

    <div class="min-h-screen w-full flex flex-col lg:flex-row">

        {{-- ── ١. لای ڕاست: بەشی فۆرمی چوونەژوورەوە (Main Form Area) ── --}}
        <div class="flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-14 xl:p-18 bg-white z-10">

            {{-- سەری پەڕە: لۆگۆ بۆ مۆبایل و ناوی سیستەم --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-blue-600 text-white font-black text-xl shadow-md shadow-blue-500/20">
                        هـ
                    </span>
                    <div>
                        <div class="font-black text-slate-900 text-base leading-tight">
                            {{ \App\Models\Setting::get('company_name', 'کارگەی ئاسنگەری هێمن') }}
                        </div>
                        <div class="text-xs text-slate-400 font-medium">سیستەمی بەڕێوەبردنی کارگە</div>
                    </div>
                </div>

                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>سیستەم ئامادەیە</span>
                </div>
            </div>

            {{-- ناوەڕۆکی فۆرم: ڕێکخراو و فراوان --}}
            <div class="my-auto py-8 max-w-md w-full mx-auto space-y-6">

                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">بەخێربێیتەوە 👋</h2>
                    <p class="text-sm text-slate-500 font-medium mt-1.5">
                        تکایە بەشەکەت دیاری بکە و پاسۆرد بنووسە بۆ چوونەژوورەوە
                    </p>
                </div>

                {{-- هەڵبژاردنی ڕۆڵ (دوو بەشە سەرەکییەکە بە کارتی گەورە و جوان) --}}
                <div class="space-y-2.5">
                    <label class="block text-xs font-bold text-slate-700">هەڵبژاردنی بەشی کار:</label>

                    <div class="grid grid-cols-2 gap-3.5">
                        {{-- بەشی بەڕێوەبەر و فرۆشتن --}}
                        <button type="button" @click="setRole('admin')"
                                :class="selectedRole === 'admin'
                                    ? 'bg-blue-50/80 border-blue-500 ring-2 ring-blue-200 shadow-sm'
                                    : 'bg-slate-50/80 border-slate-200 hover:bg-slate-100 hover:border-slate-300'"
                                class="p-4 rounded-2xl border-2 transition-all text-right flex flex-col justify-between cursor-pointer group">
                            <div class="flex items-center justify-between mb-3">
                                <div class="size-10 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110"
                                     :class="selectedRole === 'admin' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700'">
                                    🏢
                                </div>
                                <span x-show="selectedRole === 'admin'" class="size-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-bold">✓</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold" :class="selectedRole === 'admin' ? 'text-blue-900' : 'text-slate-800'">بەڕێوەبەر</div>
                                <div class="text-xs text-slate-500 mt-0.5 font-medium">ئۆفیس و فرۆشتن</div>
                            </div>
                        </button>

                        {{-- بەشی کارگە و وەستاکان --}}
                        <button type="button" @click="setRole('wasta')"
                                :class="selectedRole === 'wasta'
                                    ? 'bg-indigo-50/80 border-indigo-500 ring-2 ring-indigo-200 shadow-sm'
                                    : 'bg-slate-50/80 border-slate-200 hover:bg-slate-100 hover:border-slate-300'"
                                class="p-4 rounded-2xl border-2 transition-all text-right flex flex-col justify-between cursor-pointer group">
                            <div class="flex items-center justify-between mb-3">
                                <div class="size-10 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110"
                                     :class="selectedRole === 'wasta' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700'">
                                    ⚒️
                                </div>
                                <span x-show="selectedRole === 'wasta'" class="size-5 rounded-full bg-indigo-600 text-white text-[11px] flex items-center justify-center font-bold">✓</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold" :class="selectedRole === 'wasta' ? 'text-indigo-900' : 'text-slate-800'">وەستا</div>
                                <div class="text-xs text-slate-500 mt-0.5 font-medium">کارگە و دروستکردن</div>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- فۆرمی تێپەڕەوشە --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-4 pt-1">
                    @csrf

                    @if (isset($errors) && $errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-700 font-bold flex items-center gap-2.5">
                            <svg class="size-5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 font-medium transition-all"
                                   dir="ltr" placeholder="admin@hemin.krd">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5" for="password">وشەی نهێنی</label>
                        <div class="relative">
                            <input id="password" name="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   required autofocus
                                   x-model="password"
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 font-medium transition-all"
                                   dir="ltr" placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 text-xs cursor-pointer select-none">
                                <span x-show="!showPassword">👁️</span>
                                <span x-show="showPassword">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-0.5">
                        <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none font-medium">
                            <input type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>لەبیرم مەکە</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 px-5 rounded-xl text-sm font-black text-white shadow-lg transition-all active:scale-98 cursor-pointer mt-3"
                            :class="selectedRole === 'admin'
                                ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/25'
                                : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/25'">
                        <span x-text="selectedRole === 'admin' ? 'چوونەژوورەوە بۆ ئۆفیسی فرۆشتن 🏢' : 'چوونەژوورەوە بۆ کارگەی دروستکردن ⚒️'"></span>
                    </button>
                </form>
            </div>

            {{-- بنەوەی فۆرم: ناونیشان --}}
            <div class="pt-6 border-t border-slate-100 text-xs text-slate-400 flex flex-wrap items-center justify-between gap-2">
                <span>{{ \App\Models\Setting::get('company_address', 'هەولێر — کۆمەڵگەی پیشەسازی') }}</span>
                <span>سیستەمی هێمن v2.0</span>
            </div>
        </div>

        {{-- ── ٢. لای چەپ: پەنێڵی ناوازە و براندینگی کارگە (Brand Showcase Side) ── --}}
        <div class="hidden lg:flex flex-1 login-bg-pattern text-white p-10 xl:p-16 flex-col justify-between relative overflow-hidden">

            {{-- ڕووناکی و دیکۆراتی سەرنجڕاکێش لە باکگراوند --}}
            <div class="absolute -top-24 -left-24 size-96 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 size-96 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

            {{-- بەشی سەرەوە: ناونیشان و تاگ --}}
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full glass-card text-blue-300 text-xs font-bold border border-blue-400/20 mb-6">
                    <span>✨</span>
                    <span>سیستەمی زیرەکی بەڕێوەبردنی کارگە و فرۆشتن</span>
                </div>

                <h2 class="text-3xl xl:text-4xl font-black leading-snug tracking-tight text-white max-w-lg">
                    بەڕێوەبردنی کارگە، فرۆشتن و مەواد بە شێوازێکی خێرا و پرۆفشناڵ
                </h2>
                <p class="mt-3 text-slate-300 text-sm max-w-md leading-relaxed">
                    بەستنەوەی ئۆفیسی فرۆشتن لەگەڵ وەستاکانی کارگە لە یەک سیستەمی یەکگرتوودا بۆ خێرایی کارکردن.
                </p>
            </div>

            {{-- بەشی ناوەڕاست: کارتە تایبەتمەندەکان (Feature Glass Cards) --}}
            <div class="relative z-10 space-y-3.5 my-8 max-w-lg">
                {{-- کارتی فرۆشتن --}}
                <div class="glass-card p-4.5 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-all border border-white/10">
                    <div class="size-12 rounded-xl bg-blue-500/20 border border-blue-400/30 text-blue-300 flex items-center justify-center text-xl shrink-0">
                        📋
                    </div>
                    <div>
                        <div class="font-bold text-sm text-white">فرۆشتن، وەسڵەکان و دارایی</div>
                        <div class="text-xs text-slate-300 mt-0.5">تۆمارکردنی وەسڵ، حسابی کڕیاران و قەرزەکان بە دینار و دۆلار</div>
                    </div>
                </div>

                {{-- کارتی دروستکردن --}}
                <div class="glass-card p-4.5 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-all border border-white/10">
                    <div class="size-12 rounded-xl bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 flex items-center justify-center text-xl shrink-0">
                        ⚒️
                    </div>
                    <div>
                        <div class="font-bold text-sm text-white">داشبۆردی وەستاکان و کارگە</div>
                        <div class="text-xs text-slate-300 mt-0.5">بینینی وێنەی شتە داواکراوەکان و گۆڕینی دۆخی دروستکردن</div>
                    </div>
                </div>

                {{-- کارتی مەوادی خاو --}}
                <div class="glass-card p-4.5 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-all border border-white/10">
                    <div class="size-12 rounded-xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center text-xl shrink-0">
                        📦
                    </div>
                    <div>
                        <div class="font-bold text-sm text-white">دوو کۆگا و مەوادی دروستکردن</div>
                        <div class="text-xs text-slate-300 mt-0.5">کۆگای سەرەکی و شوێنی دروستکردن بە تۆمارکردنی کەمبوون و بەکارهێنان</div>
                    </div>
                </div>
            </div>

            {{-- بەشی خوارەوە: ماف پارێزراوە --}}
            <div class="relative z-10 text-xs text-slate-400 flex items-center justify-between">
                <span>{{ \App\Models\Setting::get('company_name', 'کارگەی ئاسنگەری هێمن') }} &copy; {{ date('Y') }}</span>
                <span class="text-slate-500 font-mono">Hemin Workshop Suite</span>
            </div>
        </div>

    </div>

</body>
</html>
