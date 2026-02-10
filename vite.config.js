import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    //  server: {
    //     host: "0.0.0.0",   // Allow network access
    //     port: 5173,        // Default Vite port
    //     strictPort: true,
    //      cors: true,
    //     hmr: {
    //         host: "192.168.1.9", // change later if needed
    //     },
    // },
});
