import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/styleDas.css',
                'resources/css/DataTableCss.css',

                'resources/img/user.jpg',
                'resources/img/favicon.png',

                'resources/js/app.js',
                'resources/js/jquery-3.7.1.min.js',
                'resources/js/DataTableJs.js',
                'resources/js/DashFunctions.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            host: process.env.RAILWAY_STATIC_URL || 'localhost',
        },
    },
    build: {
        cssCodeSplit: true,
    }
});