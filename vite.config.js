import { defineConfig } from 'vite';
import { resolve } from 'path';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => ({
    build: {
        lib: {
            entry: resolve(__dirname, 'components/index.jsx'),
            name: 'transcript',
            fileName: 'index'
        },
        outDir: 'asset/dist/',
        sourcemap: true
    },
    plugins: [ react({ jsxRuntime: "classic" }) ],
    define: {
        'process.env.NODE_ENV': `"${ mode }"`
    }
}));