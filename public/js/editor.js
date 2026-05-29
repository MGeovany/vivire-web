'use strict';
(function () {

  // ── State ────────────────────────────────────────────────────────────────────
  const saveTimers = {};   // section+date -> timeout
  const lastSaved  = {};   // section+date -> serialized JSON (dirty check)
  let   dragNode   = null;  // image node being repositioned
  const SEP_RE     = /[\s.,;:!?()\[\]{}"'¡¿—–-]/;

  function keyOf(editor) { return editor.dataset.section + '|' + editor.dataset.date; }

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  // ── Optimized auto-save (only fires when content actually changed) ──────────
  function scheduleAutoSave(editor) {
    const key  = keyOf(editor);
    const json = JSON.stringify(collectBlocks(editor));
    if (lastSaved[key] === json) return;        // nothing changed → skip entirely
    clearTimeout(saveTimers[key]);
    setIndicator('saving');
    saveTimers[key] = setTimeout(() => saveEditor(editor), 1500);
  }

  async function saveEditor(editor) {
    const key    = keyOf(editor);
    const blocks = collectBlocks(editor);
    const json   = JSON.stringify(blocks);
    if (lastSaved[key] === json) { setIndicator(''); return; }

    const payload = {
      section:    editor.dataset.section,
      entry_date: editor.dataset.date,
      blocks,
    };
    console.log('[vivire] POST /entries', payload);
    try {
      const res = await fetch('/entries', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });
      console.log('[vivire] save response', res.status);
      if (res.ok) {
        lastSaved[key] = json;
        setIndicator('saved');
        setTimeout(() => setIndicator(''), 2500);
      } else {
        const err = await readApiError(res, 'No se pudo guardar');
        console.error('[vivire] save error', err);
        setIndicator('');
        showToast(err);
      }
    } catch (e) {
      console.error('[vivire] save fetch error', e);
      setIndicator('');
      showToast('Sin conexión. Intentaremos guardar de nuevo.');
    }
  }

  // ── Block serialization ────────────────────────────────────────────────────
  function collectBlocks(editor) {
    const blocks = [];
    for (const node of editor.children) {
      if (node.classList.contains('drop-caret')) continue;
      if (node.classList.contains('block-text')) {
        const text = node.textContent.trim();
        if (text) blocks.push({ id: uid(), type: 'text', content: text, metadata: {} });
      } else if (node.classList.contains('block-media')) {
        const b = serializeMedia(node);
        if (b) blocks.push(b);
      }
    }
    return blocks;
  }

  function serializeMedia(node) {
    const type = node.dataset.blockType;
    let content = '', metadata = {};
    if (type === 'image') {
      content  = node.querySelector('img')?.src || '';
      metadata = {
        name:  node.querySelector('img')?.alt || '',
        float: node.dataset.float || 'left',
        size:  node.dataset.size  || 'm',
      };
    }
    if (type === 'audio')    content = node.querySelector('audio')?.src || '';
    if (type === 'video')    content = node.querySelector('video')?.src || '';
    if (type === 'document') {
      content  = node.querySelector('a')?.href || '';
      metadata = {
        name: node.querySelector('.block-doc-name')?.textContent || '',
        size: Number(node.querySelector('.block-doc-meta')?.dataset.size || 0),
      };
    }
    return content ? { id: node.dataset.blockId || uid(), type, content, metadata } : null;
  }

  // ── Block editor keyboard behavior ────────────────────────────────────────
  function initEditor(editor) {
    const ph = editor.querySelector('.block-text')?.dataset.placeholder || 'Escribe aquí…';
    lastSaved[keyOf(editor)] = JSON.stringify(collectBlocks(editor));

    // Enable repositioning on images rendered server-side
    editor.querySelectorAll('.block-media[data-block-type="image"]').forEach(enableImageDrag);

    editor.addEventListener('keydown', e => {
      playClick(e);

      // Accent/spell fix when a word boundary is typed (space or punctuation)
      if ((e.key === ' ' || SEP_RE.test(e.key)) && e.key.length === 1 &&
          !e.ctrlKey && !e.metaKey && !e.altKey) {
        const block = activeTextBlock();
        if (block && maybeFixWordBeforeCursor(block)) {
          e.preventDefault();
          document.execCommand('insertText', false, e.key);
          scheduleAutoSave(editor);
          return;
        }
      }

      if (e.key === 'Enter' && !e.shiftKey) {
        const block = activeTextBlock();
        if (block) {
          e.preventDefault();
          maybeFixWordBeforeCursor(block);   // fix the word we just finished
          const next = makeTextBlock(ph);
          block.after(next);
          next.focus();
          scheduleAutoSave(editor);
        }
      }
      if (e.key === 'Backspace') {
        const block = activeTextBlock();
        if (block && block.textContent === '') {
          const all = [...editor.querySelectorAll('.block-text')];
          const idx = all.indexOf(block);
          if (idx > 0) {
            e.preventDefault();
            cursorEnd(all[idx - 1]);
            block.remove();
            scheduleAutoSave(editor);
          }
        }
      }
    });

    editor.addEventListener('input', () => scheduleAutoSave(editor));

    editor.addEventListener('paste', async e => {
      const img = [...(e.clipboardData?.items || [])].find(i => i.type.startsWith('image/'));
      if (img) {
        e.preventDefault();
        const file = img.getAsFile();
        if (file) await uploadAndAppend(editor, 'image', file, null);
      }
    });

    // Drag & drop: external files (upload) + internal image repositioning
    editor.addEventListener('dragover',  e => onDragOver(e, editor));
    editor.addEventListener('dragleave', () => editor.classList.remove('drop-active'));
    editor.addEventListener('drop',      e => onDrop(e, editor));
  }

  // ── File upload ────────────────────────────────────────────────────────────
  async function uploadAndAppend(editor, type, file, target) {
    const loader = mkEl('div', 'flex items-center gap-[10px] py-3 text-sm text-dim italic');
    loader.innerHTML = '<div class="spinner"></div> Subiendo…';
    if (target && target.parentNode === editor) editor.insertBefore(loader, target);
    else editor.appendChild(loader);

    const form = new FormData();
    form.append('file', file);
    form.append('type', type);

    console.log('[vivire] POST /uploads', { type, name: file.name, size: file.size });
    try {
      const res = await fetch('/uploads', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        credentials: 'same-origin',
        body: form,
      });
      console.log('[vivire] upload response', res.status);
      if (res.ok) {
        const { url, name, size } = await res.json();
        editor.insertBefore(buildMediaNode(type, url, name, size), loader);
        loader.remove();
        scheduleAutoSave(editor);
      } else {
        loader.remove();
        const err = await readApiError(res, 'No se pudo subir el archivo');
        console.error('[vivire] upload error', err);
        showToast(err);
      }
    } catch (e) {
      loader.remove();
      console.error('[vivire] upload fetch error', e);
      showToast('Sin conexión. No se pudo subir el archivo.');
    }
  }

  function buildMediaNode(type, url, name, size) {
    const wrap = mkEl('div', (type === 'image' ? 'block-media' : 'block-media my-3 relative') + ' is-new');
    wrap.dataset.blockId   = uid();
    wrap.dataset.blockType = type;

    if (type === 'image') {
      // Random side + size → messy journal layout; text wraps around the float
      wrap.dataset.float = Math.random() < 0.5 ? 'left' : 'right';
      wrap.dataset.size  = ['s', 'm', 'l'][Math.floor(Math.random() * 3)];
      const img = document.createElement('img');
      img.src = url; img.alt = name; img.loading = 'lazy';
      img.className = 'w-full h-auto rounded-lg border border-border block';
      wrap.appendChild(img);
      enableImageDrag(wrap);
    } else if (type === 'audio') {
      const a = document.createElement('audio');
      a.src = url; a.controls = true;
      a.className = 'w-full my-1 accent-fg h-9';
      wrap.appendChild(a);
    } else if (type === 'video') {
      const v = document.createElement('video');
      v.src = url; v.controls = true; v.playsInline = true;
      v.className = 'max-w-full w-full rounded-lg border border-border block';
      wrap.appendChild(v);
    } else if (type === 'document') {
      const a = document.createElement('a');
      a.href = url; a.target = '_blank'; a.rel = 'noopener noreferrer';
      a.className = 'block-doc-card flex items-center gap-3 px-4 py-3 border border-border rounded-lg transition-[border-color,background] duration-150 hover:border-muted hover:bg-white';
      a.innerHTML =
        `<span class="block-doc-icon text-lg shrink-0">${docIcon(name)}</span>` +
        `<div class="block-doc-info min-w-0">` +
          `<div class="block-doc-name text-[13.5px] text-fg truncate">${esc(name)}</div>` +
          `<div class="block-doc-meta text-[11.5px] text-subtle mt-px" data-size="${size}">${fmtSize(size)}</div>` +
        `</div>`;
      wrap.appendChild(a);
    }
    return wrap;
  }

  // ── Media popup ────────────────────────────────────────────────────────────
  let popupDocBound = false;

  function initMediaPopups() {
    document.querySelectorAll('.add-media-btn, [title="Añadir media"]').forEach(btn => {
      if (btn.dataset.vivireReady) return;
      btn.dataset.vivireReady = '1';

      const popup = btn.nextElementSibling;
      if (!popup || !popup.classList.contains('media-popup')) return;

      btn.addEventListener('click', e => {
        e.stopPropagation();
        const isOpen = popup.classList.contains('open');
        closeAllPopups();
        if (!isOpen) popup.classList.add('open');
      });

      popup.querySelectorAll('.media-popup-item, [data-type]').forEach(item => {
        item.addEventListener('click', () => {
          closeAllPopups();
          const input  = document.createElement('input');
          input.type   = 'file';
          input.accept = item.dataset.accept || '*/*';
          input.addEventListener('change', async () => {
            const file = input.files?.[0];
            if (!file) return;
            const editor = btn.closest('[data-section]')?.querySelector('.block-editor')
                        || btn.closest('.journal-section')?.querySelector('.block-editor');
            if (editor) await uploadAndAppend(editor, item.dataset.type, file, null);
          });
          input.click();
        });
      });
    });

    if (!popupDocBound) {
      popupDocBound = true;
      document.addEventListener('click', closeAllPopups);
    }
  }

  function closeAllPopups() {
    document.querySelectorAll('.media-popup.open').forEach(p => p.classList.remove('open'));
  }

  // ── Save indicator ─────────────────────────────────────────────────────────
  function setIndicator(state) {
    const el = document.getElementById('save-indicator');
    if (!el) return;
    el.className = 'save-indicator text-[11px] text-muted min-w-[68px] text-right select-none transition-colors duration-200';
    if (state === 'saving') {
      el.textContent = 'Guardando…';
      el.classList.add('text-dim', 'is-saving');
    } else if (state === 'saved') {
      el.textContent = 'Guardado';
      el.classList.add('text-success', 'is-saved');
    } else {
      el.textContent = '';
    }
  }

  // ── Toast ──────────────────────────────────────────────────────────────────
  let toastTimer = null;
  function showToast(message) {
    if (typeof window.showToast === 'function') {
      window.showToast(message, 'error');
      return;
    }
    const el = document.getElementById('app-toast');
    if (!el) return;
    el.textContent = String(message || '').trim() || 'Error';
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 3500);
  }

  async function readApiError(res, fallback) {
    try {
      const data = await res.json();
      if (data && typeof data.error === 'string' && data.error.trim()) return data.error.trim();
    } catch { /* ignore */ }
    return fallback || 'Error';
  }

  // ── Utils ──────────────────────────────────────────────────────────────────
  function makeTextBlock(placeholder) {
    const d = document.createElement('div');
    d.className       = 'block-text w-full font-write text-[17px] font-normal leading-[1.75] text-fg outline-none border-none bg-transparent py-0.5 caret-accent break-words whitespace-pre-wrap max-sm:text-[16px]';
    d.contentEditable = 'true';
    d.dataset.placeholder = placeholder;
    return d;
  }

  function activeTextBlock() {
    return window.getSelection()?.anchorNode?.parentElement?.closest('.block-text') ?? null;
  }

  function cursorEnd(node) {
    const r = document.createRange(), s = window.getSelection();
    r.selectNodeContents(node); r.collapse(false);
    s.removeAllRanges(); s.addRange(r);
  }

  function mkEl(tag, cls) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    return n;
  }

  function uid() { return Math.random().toString(36).slice(2, 10) + Date.now().toString(36); }

  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function fmtSize(b) {
    if (!b) return '';
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
    return (b / 1048576).toFixed(1) + ' MB';
  }

  function docIcon(name) {
    const ext = (name || '').split('.').pop().toLowerCase();
    if (ext === 'pdf') return '📕';
    if (['doc','docx'].includes(ext)) return '📝';
    if (['xls','xlsx','csv'].includes(ext)) return '📊';
    if (['ppt','pptx'].includes(ext)) return '📋';
    if (['zip','rar','7z','tar','gz'].includes(ext)) return '🗜';
    return '📄';
  }

  // ── Spell check (Hunspell via nspell) ─────────────────────────────────────
  function getWordBeforeCursor(block) {
    const sel = window.getSelection();
    if (!sel?.rangeCount) return null;
    const range = sel.getRangeAt(0);
    if (!range.collapsed || !block.contains(range.endContainer)) return null;

    const pre = range.cloneRange();
    pre.selectNodeContents(block);
    pre.setEnd(range.endContainer, range.endOffset);
    const before = pre.toString();
    const match = before.match(/([\p{L}']+)$/u);
    if (!match) return null;

    const word = match[1];
    return { word, start: before.length - word.length, end: before.length };
  }

  function replaceWordInBlock(block, start, end, replacement) {
    const walker = document.createTreeWalker(block, NodeFilter.SHOW_TEXT);
    let pos = 0;
    let startNode, startOff, endNode, endOff;

    while (walker.nextNode()) {
      const node = walker.currentNode;
      const len = node.length;
      if (startNode === undefined && pos + len > start) {
        startNode = node;
        startOff = start - pos;
      }
      if (endNode === undefined && pos + len >= end) {
        endNode = node;
        endOff = end - pos;
        break;
      }
      pos += len;
    }
    if (!startNode || !endNode) return false;

    const r = document.createRange();
    r.setStart(startNode, startOff);
    r.setEnd(endNode, endOff);
    r.deleteContents();
    const textNode = document.createTextNode(replacement);
    r.insertNode(textNode);

    const sel = window.getSelection();
    sel.removeAllRanges();
    const after = document.createRange();
    after.setStart(textNode, textNode.length);
    after.collapse(true);
    sel.addRange(after);
    return true;
  }

  function maybeFixWordBeforeCursor(block) {
    const spell = window.VivireSpell;
    if (!spell?.fix) return false;

    const hit = getWordBeforeCursor(block);
    if (!hit) return false;

    const fixed = spell.fix(hit.word);
    if (fixed === hit.word) return false;

    return replaceWordInBlock(block, hit.start, hit.end, fixed);
  }

  // ── Typing sounds (synthesized — no audio files) ────────────────────────────
  let audioCtx = null;
  let soundOn  = localStorage.getItem('vivire_sound') !== 'off';   // default ON

  function playClick(e) {
    if (!soundOn) return;
    const k = e.key;
    const printable = (k && k.length === 1) || k === 'Backspace' || k === 'Enter';
    if (!printable) return;
    try {
      audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') audioCtx.resume();
      const t    = audioCtx.currentTime;
      const osc  = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      // Lower thock for Enter/Space, crisper click for letters
      const base = (k === 'Enter' || k === ' ') ? 130 : 200;
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(base + Math.random() * 50, t);
      gain.gain.setValueAtTime(0.0001, t);
      gain.gain.exponentialRampToValueAtTime(0.07, t + 0.004);
      gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.055);
      osc.connect(gain).connect(audioCtx.destination);
      osc.start(t);
      osc.stop(t + 0.06);
    } catch (_) { /* audio unavailable — ignore */ }
  }

  // Lucide icons: volume-2 (on) / volume-x (off)
  const LUCIDE_VOLUME_2 = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></svg>';
  const LUCIDE_VOLUME_X = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><line x1="22" x2="16" y1="9" y2="15"/><line x1="16" x2="22" y1="9" y2="15"/></svg>';

  function initSoundToggle() {
    const btn = document.getElementById('sound-toggle');
    if (!btn || btn.dataset.vivireReady) return;
    btn.dataset.vivireReady = '1';

    const paint = () => {
      btn.innerHTML = soundOn ? LUCIDE_VOLUME_2 : LUCIDE_VOLUME_X;
      btn.title = soundOn ? 'Sonido de tecleo: activado' : 'Sonido de tecleo: silenciado';
      btn.classList.toggle('text-fg', soundOn);
      btn.classList.toggle('text-muted', !soundOn);
    };
    paint();
    btn.addEventListener('click', () => {
      soundOn = !soundOn;
      localStorage.setItem('vivire_sound', soundOn ? 'on' : 'off');
      if (soundOn && audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
      paint();
    });
  }

  // ── Image drag-to-reposition (text reflows around the float) ────────────────
  function enableImageDrag(node) {
    node.setAttribute('draggable', 'true');
    node.addEventListener('dragstart', e => {
      dragNode = node;
      node.classList.add('dragging');
      if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', 'vivire-image'); } catch (_) {}
      }
    });
    node.addEventListener('dragend', () => {
      node.classList.remove('dragging');
      dragNode = null;
      clearCaret();
    });
  }

  function onDragOver(e, editor) {
    const types = e.dataTransfer ? [...e.dataTransfer.types] : [];
    const hasFiles = types.includes('Files');
    if (!hasFiles && !dragNode) return;
    e.preventDefault();
    if (hasFiles) {
      editor.classList.add('drop-active');
      e.dataTransfer.dropEffect = 'copy';
    } else {
      e.dataTransfer.dropEffect = 'move';
      showCaretAt(editor, e.clientY);
    }
  }

  async function onDrop(e, editor) {
    const types = e.dataTransfer ? [...e.dataTransfer.types] : [];
    const hasFiles = types.includes('Files') && e.dataTransfer.files.length;
    e.preventDefault();
    editor.classList.remove('drop-active');
    const target = insertionTarget(editor, e.clientY);
    clearCaret();

    if (hasFiles) {
      for (const file of e.dataTransfer.files) {
        const type = file.type.startsWith('image/') ? 'image'
                   : file.type.startsWith('audio/') ? 'audio'
                   : file.type.startsWith('video/') ? 'video'
                   : 'document';
        await uploadAndAppend(editor, type, file, target);
      }
      return;
    }

    if (dragNode && dragNode.parentNode === editor) {
      if (target && target !== dragNode) editor.insertBefore(dragNode, target);
      else if (!target) editor.appendChild(dragNode);
      scheduleAutoSave(editor);
    }
    dragNode = null;
  }

  // Block nearest to the drop point — insert before it (null = drop at the end)
  function insertionTarget(editor, y) {
    const blocks = [...editor.children].filter(n =>
      !n.classList.contains('drop-caret') &&
      (n.classList.contains('block-text') || n.classList.contains('block-media')));
    for (const b of blocks) {
      const r = b.getBoundingClientRect();
      if (y < r.top + r.height / 2) return b;
    }
    return null;
  }

  let caretEl = null;
  function showCaretAt(editor, y) {
    const target = insertionTarget(editor, y);
    if (!caretEl) { caretEl = document.createElement('div'); caretEl.className = 'drop-caret'; }
    if (target) editor.insertBefore(caretEl, target);
    else editor.appendChild(caretEl);
  }
  function clearCaret() { if (caretEl && caretEl.parentNode) caretEl.parentNode.removeChild(caretEl); }

  // ── Boot ───────────────────────────────────────────────────────────────────
  // Runs on first load AND on Livewire SPA navigation (login redirects with
  // navigate:true, so DOMContentLoaded never fires again on the journal page).
  // Idempotent: editors/buttons are flagged so re-runs don't double-bind.
  let spellInitStarted = false;

  function boot() {
    if (!spellInitStarted) {
      spellInitStarted = true;
      Promise.resolve(window.VivireSpell?.init?.()).catch(err =>
        console.warn('[vivire] corrector ortográfico no disponible', err));
    }
    document.querySelectorAll('.block-editor:not(.locked)').forEach(editor => {
      if (editor.dataset.vivireReady) return;
      editor.dataset.vivireReady = '1';
      initEditor(editor);
    });
    initMediaPopups();
    initSoundToggle();
  }

  document.addEventListener('DOMContentLoaded', boot);
  document.addEventListener('livewire:navigated', boot);  // Livewire SPA navigation

})();
