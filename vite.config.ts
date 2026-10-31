import { svelte } from '@sveltejs/vite-plugin-svelte';
import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [svelte()],
    // Public files are served by PHP (public/ is the document root); Vite
    // only compiles the module graph into public/build.
    publicDir: false,
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        sourcemap: false,
        target: 'es2020',
        assetsDir: 'assets',
        rollupOptions: {
            input: fileURLToPath(new URL('./src/main.ts', import.meta.url)),
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
});
