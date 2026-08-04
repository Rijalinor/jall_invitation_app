import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/invitation-templates/elegant-rose/assets/theme.css',
                'resources/invitation-templates/elegant-rose/assets/theme.js',
                'resources/invitation-templates/midnight-ledger/assets/theme.css',
                'resources/invitation-templates/midnight-ledger/assets/theme.js',
                'resources/invitation-templates/borneo-nocturne/assets/theme.css',
                'resources/invitation-templates/borneo-nocturne/assets/theme.js',
                'resources/invitation-templates/borneo-nocturne/assets/gsap.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
