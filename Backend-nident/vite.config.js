import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    // Local: "public". cPanel: VITE_PUBLIC_DIR=../public_html en .env
    const publicDirectory = env.VITE_PUBLIC_DIR || 'public';

    return {
        plugins: [
            laravel({
                input: [
                //add
                'resources/sass/index.scss',
                'resources/sass/web_shopDetail.scss',
                'resources/sass/web_shopDetail_lista.scss',
                'resources/sass/web_shopResultado.scss',
                'resources/sass/login.scss',
                'resources/sass/whatsApp.scss',
                'resources/sass/customise_01.scss',
                'resources/sass/web_about.scss',
                'resources/sass/web_contact.scss',
                'resources/sass/web_gallery.scss',
                'resources/sass/web_checkout.scss',
                'resources/sass/web_footer.scss',
                'resources/sass/web_reclamos_estado.scss',
                'resources/sass/web_customizer.scss',

                'resources/js/web_shopDetail.js',
                'resources/js/web_checkout.js',
                'resources/js/index.js',
                'resources/js/web_contact.js',
                'resources/js/customise_01.js',
                'resources/js/web_customizer.js',
                'resources/js/web_cart_global.js',

            ],
            publicDirectory,
            refresh: publicDirectory === 'public',
        }),
    ],
    };
});
