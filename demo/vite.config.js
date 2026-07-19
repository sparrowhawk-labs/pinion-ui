import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath } from 'node:url';

const nm = fileURLToPath(new URL('./node_modules/', import.meta.url));

// editor.js lives in ../src (outside this demo root), so its bare tiptap imports
// resolve from there and miss demo/node_modules. Alias them explicitly.
const tiptap = [
  '@tiptap/core', '@tiptap/starter-kit', '@tiptap/extension-placeholder',
  '@tiptap/extension-task-list', '@tiptap/extension-task-item', '@tiptap/extension-link',
  '@tiptap/extension-image',
  'alpinejs',
];

export default defineConfig({
  plugins: [tailwindcss()],
  server: { port: 5275, strictPort: true, fs: { allow: ['..'] } },
  resolve: {
    alias: Object.fromEntries(tiptap.map(p => [p, nm + p])),
  },
});
