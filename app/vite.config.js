import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Template editor CSS (JS bundled with app.js)
                'resources/css/editor.css',
            ],
            refresh: true,
        }),
    ],
});
