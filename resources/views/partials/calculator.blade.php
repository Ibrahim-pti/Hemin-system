{{-- حاسیبە — لە هەموو لاپەڕەیەکەوە بەردەستە. --}}
<div x-data="calculator()" x-cloak
     @open-calculator.window="open = true"
     @keydown.window.escape="open = false">

    <div x-show="open" class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center no-print"
         @click.self="open = false">

        <div class="w-full max-w-xs rounded-[--radius-card] border border-[--color-line] bg-[--color-surface]">
            <div class="flex items-center justify-between border-b border-[--color-line] px-4 py-3">
                <span class="text-sm font-semibold">حاسیبە</span>
                <button @click="open = false" class="text-[--color-ink-soft] hover:text-[--color-ink]">✕</button>
            </div>

            <div class="p-3">
                {{-- پیشاندەر --}}
                <div class="mb-3 rounded-md border border-[--color-line] bg-[--color-surface-soft] p-3">
                    <div class="num h-4 text-xs text-[--color-ink-soft]" x-text="history"></div>
                    <div class="num truncate text-2xl font-semibold" x-text="display"></div>
                </div>

                {{-- دوگمەکان --}}
                <div class="grid grid-cols-4 gap-2">
                    <template x-for="key in keys" :key="key.label">
                        <button type="button" @click="press(key)"
                                :class="key.style"
                                class="rounded-md border border-[--color-line] py-3 text-sm font-medium transition-colors"
                                x-text="key.label"></button>
                    </template>
                </div>

                <p class="mt-3 text-center text-xs text-[--color-ink-soft]">
                    ئەنجام: <span class="num" x-text="formatted"></span>
                </p>
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
            { label: 'C',  action: 'clear',  style: 'bg-[--color-danger-soft] text-[--color-danger] hover:bg-[--color-surface-soft]' },
            { label: '⌫',  action: 'back',   style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '%',  action: 'op', op: '%',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '÷',  action: 'op', op: '/',  style: 'bg-[--color-surface-soft] font-semibold hover:bg-[--color-surface]' },
            { label: '7',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '8',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '9',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '×',  action: 'op', op: '*',  style: 'bg-[--color-surface-soft] font-semibold hover:bg-[--color-surface]' },
            { label: '4',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '5',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '6',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '−',  action: 'op', op: '-',  style: 'bg-[--color-surface-soft] font-semibold hover:bg-[--color-surface]' },
            { label: '1',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '2',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '3',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '+',  action: 'op', op: '+',  style: 'bg-[--color-surface-soft] font-semibold hover:bg-[--color-surface]' },
            { label: '0',  action: 'num',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '.',  action: 'dot',  style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '±',  action: 'sign', style: 'bg-[--color-surface] hover:bg-[--color-surface-soft]' },
            { label: '=',  action: 'eq',   style: '!bg-[--color-brand-700] !border-[--color-brand-700] font-semibold text-white' },
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
                // ڕێژەی سەدی: ٥٠٠ % ١٠ = ٥٠ (١٠٪ی ٥٠٠)
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
