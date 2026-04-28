import { readFileSync } from 'node:fs';
import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

const packageJson = JSON.parse(
    readFileSync(new URL('./package.json', import.meta.url), 'utf8'),
);
const appVersion =
    typeof packageJson.version === 'string' && packageJson.version !== ''
        ? packageJson.version
        : '0.0.0';

function envFlag(value) {
    const v = String(value ?? '')
        .trim()
        .toLowerCase();
    return v === '1' || v === 'true' || v === 'yes';
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const noAutoReload = envFlag(env.LARAVEL_VITE_NO_AUTO_RELOAD);

    return {
        define: {
            __APP_VERSION__: JSON.stringify(appVersion),
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                /** Full-page reload when PHP/Blade/etc. change; set LARAVEL_VITE_NO_AUTO_RELOAD=1 to disable. */
                refresh: !noAutoReload,
            }),
            react(),
            tailwindcss(),
        ],
        server: {
            /** WebSocket HMR. When false, @vitejs/plugin-react skips Fast Refresh (see configResolved). */
            hmr: noAutoReload ? false : true,
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});

