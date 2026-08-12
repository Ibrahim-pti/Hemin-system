import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // فۆنتی Vazirmatn لە خۆماڵییەوە دێت (node_modules) نەک لە CDN —
            // چونکە کارگەکە لەوانەیە بێ ئینتەرنێت کاربکات.
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
