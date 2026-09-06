import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import purgecss from 'vite-plugin-purgecss';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/views/themes/xylo/sass/app.scss',
                'resources/views/themes/xylo/js/app.js',
                'resources/views/themes/xylo/css/animate.min.css',
                'resources/views/themes/xylo/css/slick.css',
                'resources/views/themes/xylo/css/style.css',
                'resources/views/themes/xylo/css/custom.css',
                'resources/views/themes/xylo/css/design-system.css',
                'resources/views/themes/xylo/js/main.js',
                'resources/views/themes/xylo/js/slick.min.js',
            ],
            refresh: true,
        }),
        // PurgeCSS for production - removes unused CSS
        purgecss({
            enabled: process.env.NODE_ENV === 'production',
            content: [
                './resources/views/**/*.blade.php',
                './resources/js/**/*.js',
            ],
            safelist: {
                // Preserve dynamically generated classes
                standard: [/^btn-/, /^bg-/, /^text-/, /^border-/, /^badge-/, /^alert-/, /^modal-/, /^dropdown-/, /^tooltip-/, /^popover-/, /^collapse-/, /^nav-/, /^tab-/, /^carousel-/, /^form-/, /^input-/, /^select-/, /^btn-/, /^col-/, /^row-/, /^d-/, /^flex-/, /^justify-/, /^align-/, /^gap-/, /^m-/, /^p-/, /^w-/, /^h-/, /^position-/, /^top-/, /^bottom-/, /^start-/, /^end-/, /^translate-/, /^rotate-/, /^scale-/, /^opacity-/, /^visible-/, /^invisible-/, /^overflow-/, /^z-/, /^shadow-/, /^rounded-/, /^border-/, /^outline-/, /^focus-/, /^hover-/, /^active-/, /^disabled-/, /^sr-only/, /^sr-only-focusable/],
                deep: [/^animate-/, /^fade/, /^slide/, /^zoom/, /^bounce/, /^pulse/, /^spin/, /^ping/],
                greedy: [/^fa-/, /^bi-/, /^icon-/, /^toast-/, /^select2-/, /^dataTables/, /^dt-/, /^sorting/],
            },
        }),
    ],
});
