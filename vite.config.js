import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react'; // <--- Importe isso

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/mapa-reservas.jsx', // <--- Vamos criar esse arquivo de entrada específico
            ],
            refresh: true,
        }),
        react(), // <--- Adicione isso
    ],
});