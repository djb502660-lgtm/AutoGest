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
        host: 'localhost',
        port: 5173,
        strictPort: true,
<<<<<<< Updated upstream
        cors: true,
=======
        cors: {
            origin: ['http://localhost:8000', 'http://127.0.0.1:8000'],
            methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        },
        hmr: {
            host: 'localhost',
            protocol: 'ws',
            clientPort: 5173,
        },
>>>>>>> Stashed changes
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
