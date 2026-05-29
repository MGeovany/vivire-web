import * as esbuild from 'esbuild';
import { copyFileSync } from 'node:fs';

await esbuild.build({
  entryPoints: ['resources/js/toast.jsx'],
  outfile: 'public/js/toast.js',
  bundle: true,
  format: 'iife',
  minify: true,
  jsx: 'automatic',
  jsxImportSource: 'react',
  define: {
    'process.env.NODE_ENV': '"production"',
  },
});

copyFileSync('node_modules/sonner/dist/styles.css', 'public/css/sonner.css');

console.log('Built public/js/toast.js + public/css/sonner.css');
