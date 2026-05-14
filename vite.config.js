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
    const isDocker = envFlag(env.DOCKER);
    const hmrHost = env.VITE_DOCKER_HMR_HOST || 'localhost';
    /** Host port mapped to Vite in Docker (compose `VITE_PORT`, default 5173). WebSocket HMR uses this; HTTP modules still use `VITE_DEV_SERVER_URL` (nginx :8080). */
    const vitePublishedOnHost = isDocker
        ? Number(env.VITE_DOCKER_PUBLISHED_PORT || env.VITE_PORT || 5173)
        : 5173;
    const devPublicOrigin = isDocker
        ? (env.VITE_DEV_SERVER_URL ||
            `http://${hmrHost}:${env.HTTP_PORT || '8080'}`)
        : undefined;

    const server = isDocker
        ? {
              origin: devPublicOrigin,
              host: true,
              /** Allow requests when the browser uses Host: localhost:8080 and nginx proxies to :5173. */
              allowedHosts: true,
              /** Let the page on :8080 talk to the dev server / WS on the published Vite port. */
              cors: true,
              port: 5173,
              strictPort: true,
              hmr: noAutoReload
                  ? false
                  : {
                        protocol: 'ws',
                        host: hmrHost,
                        /** Inside the container Vite always listens here. */
                        port: 5173,
                        /** Browser opens ws://host:thisPort (compose host mapping `VITE_PORT`). */
                        clientPort: vitePublishedOnHost,
                    },
              watch: {
                  ignored: ['**/storage/framework/views/**'],
                  ...(envFlag(env.CHOKIDAR_USEPOLLING) ? { usePolling: true } : {}),
              },
          }
        : {
              /** WebSocket HMR. When false, @vitejs/plugin-react skips Fast Refresh (see configResolved). */
              hmr: noAutoReload ? false : true,
              watch: {
                  ignored: ['**/storage/framework/views/**'],
              },
          };

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
        server,
    };
});

