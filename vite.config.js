import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        host: true,
        strictPort: true,
        port: 5173,
        hmr: {
            host: process.env.DDEV_HOSTNAME,
            protocol: 'wss'
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
