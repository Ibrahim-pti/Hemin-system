{{-- حاسیبە — لە هەموو لاپەڕەیەکەوە بەردەستە --}}
<div x-data="calculator()" x-cloak
     @open-calculator.window="open = true"
     @keydown.window.escape="open = false">

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 no-print"
         @click.self="open = false">

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-xs bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden">
            
            {{-- سەردێڕی حاسیبە --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5 bg-slate-50/80">
                <div class="flex items-center gap-2">
                    <span class="text-base">🧮</span>
                    <span class="text-xs font-black text-slate-800">حاسیبە</span>
                </div>
                <button @click="open = false" class="w-7 h-7 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center font-bold text-sm transition-colors cursor-pointer">✕</button>
            </div>

            <div class="p-4 space-y-3.5 bg-white">
                {{-- شاشەی ژمارەکان --}}
                <div class="rounded-2xl bg-slate-900 p-4 shadow-inner text-left font-mono">
                    <div class="h-4 text-[11px] font-medium text-slate-400 text-right overflow-hidden" x-text="history || '&nbsp;'"></div>
                    <div class="truncate text-3xl font-black text-white text-right tracking-wider mt-1" x-text="display"></div>
                </div>

                {{-- دوگمەکانی حاسیبە --}}
                <div class="grid grid-cols-4 gap-2">
                    <template x-for="key in keys" :key="key.label">
                        <button type="button" @click="press(key)"
                                :class="key.style"
                                class="rounded-xl py-3 text-sm font-black transition-all cursor-pointer select-none active:scale-95"
                                x-text="key.label"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculator() {
    return {
        open: false,
        display: '0',
        history: '',
        previous: null,
        operator: null,
        fresh: true,

        keys: [
            { label: 'C',  action: 'clear',  style: 'bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200' },
            { label: '⌫',  action: 'back',   style: 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' },
            { label: '%',  action: 'op', op: '%',  style: 'bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200' },
            { label: '÷',  action: 'op', op: '/',  style: 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs' },
            
            { label: '7',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '8',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '9',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '×',  action: 'op', op: '*',  style: 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs' },
            
            { label: '4',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '5',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '6',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '−',  action: 'op', op: '-',  style: 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs' },
            
            { label: '1',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '2',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '3',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '+',  action: 'op', op: '+',  style: 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs' },
            
            { label: '0',  action: 'num',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '.',  action: 'dot',  style: 'bg-slate-50 hover:bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs' },
            { label: '±',  action: 'sign', style: 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' },
            { label: '=',  action: 'eq',   style: 'bg-emerald-600 hover:bg-emerald-700 text-white font-black shadow-md' },
        ],

        get formatted() {
            const n = parseFloat(this.display);
            return isNaN(n) ? '—' : n.toLocaleString('en-US', { maximumFractionDigits: 4 });
        },

        press(key) {
            switch (key.action) {
                case 'num':  this.digit(key.label); break;
                case 'dot':  this.dot(); break;
                case 'op':   this.setOperator(key.op); break;
                case 'eq':   this.equals(); break;
                case 'clear': this.clear(); break;
                case 'back': this.backspace(); break;
                case 'sign': this.toggleSign(); break;
            }
        },

        digit(d) {
            if (this.fresh || this.display === '0') {
                this.display = d;
                this.fresh = false;
            } else {
                this.display += d;
            }
        },

        dot() {
            if (this.fresh) { this.display = '0.'; this.fresh = false; return; }
            if (!this.display.includes('.')) this.display += '.';
        },

        setOperator(op) {
            if (this.operator && !this.fresh) this.equals();
            this.previous = parseFloat(this.display);
            this.operator = op;
            this.history = this.previous.toLocaleString('en-US') + ' ' + op;
            this.fresh = true;
        },

        equals() {
            if (this.operator === null) return;

            const current = parseFloat(this.display);
            let result;

            switch (this.operator) {
                case '+': result = this.previous + current; break;
                case '-': result = this.previous - current; break;
                case '*': result = this.previous * current; break;
                case '/': result = current === 0 ? NaN : this.previous / current; break;
                case '%': result = this.previous * current / 100; break;
            }

            this.history = '';
            this.display = isNaN(result) ? 'هەڵە' : String(Math.round(result * 1e6) / 1e6);
            this.operator = null;
            this.previous = null;
            this.fresh = true;
        },

        clear() {
            this.display = '0';
            this.history = '';
            this.previous = null;
            this.operator = null;
            this.fresh = true;
        },

        backspace() {
            this.display = this.display.length > 1 ? this.display.slice(0, -1) : '0';
        },

        toggleSign() {
            this.display = this.display.startsWith('-') ? this.display.slice(1) : '-' + this.display;
        },
    }
}
</script>
