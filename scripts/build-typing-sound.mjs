import * as esbuild from 'esbuild';

await esbuild.build({
  entryPoints: ['resources/js/typing-sound.js'],
  outfile: 'public/js/typing-sound.js',
  bundle: true,
  format: 'iife',
  minify: true,
});

console.log('Built public/js/typing-sound.js');
