import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'public/build',
    manifest: 'manifest.json',
    emptyOutDir: true,
    rollupOptions: {
      input: [
        'resources/css/app.css',
        'resources/css/features.css',
        'resources/css/luxury-apex.css',
        'resources/js/app.js',
        'resources/js/hero-3d.js',
        'resources/js/studio.js',
        'resources/js/pwa.js',
        'resources/js/gold-market.js',
        'resources/js/luxury-apex.js',
        'resources/js/gold-intelligence.js'
      ]
    }
  }
});
