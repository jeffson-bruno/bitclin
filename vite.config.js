import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
    /*build: {
        outDir: 'public/build',   // Tudo vai para public/build
        manifest: true,           // Cria manifest.json aqui dentro
        emptyOutDir: true,        // Limpa antes de compilar
        assetsDir: '',            // Nenhuma subpasta de assets — tudo direto em build/
        rollupOptions: {
            input: 'resources/js/app.js',
            output: {
                entryFileNames: '[name]-[hash].js',
                chunkFileNames: '[name]-[hash].js',
                assetFileNames: '[name]-[hash][extname]',
            },
        },
    },*/
    server: {
        hmr: {
            overlay: false,
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
