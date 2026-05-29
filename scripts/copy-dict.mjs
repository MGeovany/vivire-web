import { cpSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const src  = join(root, 'node_modules', 'dictionary-es');
const dest = join(root, 'public', 'dict', 'es');

mkdirSync(dest, { recursive: true });
cpSync(join(src, 'index.aff'), join(dest, 'index.aff'));
cpSync(join(src, 'index.dic'), join(dest, 'index.dic'));
console.log('Copied Spanish Hunspell dictionary → public/dict/es/');
