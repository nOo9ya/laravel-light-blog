import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/postcss";
import autoprefixer from "autoprefixer";
import cssnano from 'cssnano';


export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        // 프로덕션 빌드 최적화
        minify: 'esbuild',
        target: 'es2015',
        cssMinify: true,
        sourcemap: false,
        rollupOptions: {
            output: {
                // 청크 분할 최적화
                manualChunks: {
                    vendor: ['alpinejs'],
                },
                // 파일명 최적화
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    const extType = assetInfo.name.split('.')[1];
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                        return `images/[name]-[hash][extname]`;
                    }
                    if (/css/i.test(extType)) {
                        return `css/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
            },
        },
        // 압축 최적화
        assetsInlineLimit: 4096,
        // CSS 코드 분할
        cssCodeSplit: true,
    },
    // 개발 서버 설정
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    // CSS 전처리기 설정
    css: {
        devSourcemap: true,
        postcss: {
            plugins: [
                tailwindcss,
                autoprefixer,
                cssnano({
                    preset: 'default',
                }),
            ],
        },
    },
});
