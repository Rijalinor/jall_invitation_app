import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/invitations.css',
                'resources/js/invitation-hosts.js',
                'resources/js/invitation-forms.js',
                'resources/js/invitation-lightbox.js',
                'resources/invitation-templates/elegant-rose/assets/theme.css',
                'resources/invitation-templates/elegant-rose/assets/theme.js',
                'resources/invitation-templates/midnight-ledger/assets/theme.css',
                'resources/invitation-templates/midnight-ledger/assets/theme.js',
                'resources/invitation-templates/fun-storybook/assets/theme.css',
                'resources/invitation-templates/fun-storybook/assets/theme.js',
                'resources/invitation-templates/coastal-vow/assets/theme.css',
                'resources/invitation-templates/coastal-vow/assets/theme.js',
                'resources/invitation-templates/celestial-vow/assets/theme.css',
                'resources/invitation-templates/celestial-vow/assets/theme.js',
                'resources/invitation-templates/sari-pura/assets/theme.css',
                'resources/invitation-templates/sari-pura/assets/theme.js',
                'resources/invitation-templates/mahligai/assets/theme.css',
                'resources/invitation-templates/mahligai/assets/theme.js',
                'resources/invitation-templates/lentera-senja/assets/theme.css',
                'resources/invitation-templates/lentera-senja/assets/theme.js',
                'resources/invitation-templates/deco-gala/assets/theme.css',
                'resources/invitation-templates/deco-gala/assets/theme.js',
                'resources/invitation-templates/aquarelle/assets/theme.css',
                'resources/invitation-templates/aquarelle/assets/theme.js',
                'resources/invitation-templates/origami/assets/theme.css',
                'resources/invitation-templates/origami/assets/theme.js',
                'resources/invitation-templates/jejak-perjalanan/assets/theme.css',
                'resources/invitation-templates/jejak-perjalanan/assets/theme.js',
                'resources/invitation-templates/enso/assets/theme.css',
                'resources/invitation-templates/enso/assets/theme.js',
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
