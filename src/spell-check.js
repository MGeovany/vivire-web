import nspell from 'nspell';

/** @type {import('nspell').default | null} */
let spell = null;
/** @type {Promise<void> | null} */
let loading = null;

const COMBINING = /[\u0300-\u036f]/g;

function strip(w) {
  return w.normalize('NFD').replace(COMBINING, '').toLowerCase();
}

function applyCase(src, target) {
  if (src === src.toUpperCase() && src !== src.toLowerCase()) return target.toUpperCase();
  if (src[0] === src[0].toUpperCase() && src[0] !== src[0].toLowerCase()) {
    return target[0].toUpperCase() + target.slice(1);
  }
  return target;
}

/** Prefer accent-only fixes; avoid swapping to unrelated words. */
function pickSuggestion(word, suggestions) {
  if (!suggestions?.length) return null;
  const key = strip(word);
  const accentOnly = suggestions.find(
    (s) => strip(s) === key && s.toLowerCase() !== word.toLowerCase()
  );
  if (accentOnly) return accentOnly;
  if (suggestions.length === 1) return suggestions[0];
  return null;
}

async function init() {
  if (spell) return;
  if (loading) return loading;

  loading = (async () => {
    const [affRes, dicRes] = await Promise.all([
      fetch('/dict/es/index.aff'),
      fetch('/dict/es/index.dic'),
    ]);
    if (!affRes.ok || !dicRes.ok) {
      throw new Error('No se pudo cargar el diccionario español');
    }

    spell = nspell({
      aff: new Uint8Array(await affRes.arrayBuffer()),
      dic: new Uint8Array(await dicRes.arrayBuffer()),
    });
  })();

  return loading;
}

/** Returns corrected word, or the same word if no safe fix applies. */
function fix(word) {
  if (!spell || !word || word.length < 2) return word;
  if (/[0-9@#\\/]/.test(word)) return word;
  if (spell.correct(word)) return word;

  const suggestion = pickSuggestion(word, spell.suggest(word));
  return suggestion ? applyCase(word, suggestion) : word;
}

function ready() {
  return loading;
}

export { init, fix, ready };
