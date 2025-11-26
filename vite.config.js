import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/login.css',       
                'resources/js/login.js',         
                'resources/css/rider-dashboard.css',
                'resources/js/rider-dashboard.js',
            ],
            refresh: true,
        }),
    ],
});
