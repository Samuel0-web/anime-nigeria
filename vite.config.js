import { defineConfig } from 'vite';
import react from "@vitejs/plugin-react";
import path from 'path';

export default defineConfig(({ command }) => ({
    plugins: [react()],
    base: command === 'build' ? '/build/' : '/',
    publicDir: false,
    
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources'),
        },
    },

    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
            },
        },
    },

    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        cors: true,
        origin: 'http://127.0.0.1:5173',
    },

    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                public: path.resolve(__dirname, 'resources/js/public.js'),
                member: path.resolve(__dirname, 'resources/js/member.js'),
                memberReact: path.resolve(__dirname, "resources/js/react/main.jsx"),
                admin: path.resolve(__dirname, 'resources/js/admin.js'),
            },
        },
    },
}));