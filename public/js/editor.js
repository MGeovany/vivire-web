'use strict';
(function () {

  // ── Auto-save ──────────────────────────────────────────────────────────────
  const saveTimers = {};

  function scheduleAutoSave(editor) {
    const key = editor.dataset.section + editor.dataset.date;
    clearTimeout(saveTimers[key]);
    setIndicator('saving');
    saveTimers[key] = setTimeout(() => saveEditor(editor), 1500);
  }

  async function saveEditor(editor) {
    const blocks = collectBlocks(editor);
    const payload = {
      section:    editor.dataset.section,
      entry_date: editor.dataset.date,
      blocks,
    };
    console.log('[vivire] POST /api/save', payload);
    try {
      const res = await fetch('/api/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      console.log('[vivire] save response', res.status);
      if (res.ok) {
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
    if (type === 'image')    content = node.querySelector('img')?.src   || '';
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

    editor.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        const block = activeTextBlock();
        if (block) {
          e.preventDefault();
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
        if (file) await uploadAndAppend(editor, 'image', file);
      }
    });
  }

  // ── File upload ────────────────────────────────────────────────────────────
  async function uploadAndAppend(editor, type, file) {
    const loader = mkEl('div', 'flex items-center gap-[10px] py-3 text-sm text-dim italic');
    loader.innerHTML = '<div class="spinner"></div> Subiendo…';
    editor.appendChild(loader);

    const form = new FormData();
    form.append('file', file);
    form.append('type', type);

    console.log('[vivire] POST /api/upload', { type, name: file.name, size: file.size });
    try {
      const res = await fetch('/api/upload', { method: 'POST', body: form });
      loader.remove();
      console.log('[vivire] upload response', res.status);
      if (res.ok) {
        const { url, name, size } = await res.json();
        editor.appendChild(buildMediaNode(type, url, name, size));
        scheduleAutoSave(editor);
      } else {
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
    const wrap = mkEl('div', 'block-media my-3 relative');
    wrap.dataset.blockId   = uid();
    wrap.dataset.blockType = type;

    if (type === 'image') {
      const img = document.createElement('img');
      img.src = url; img.alt = name; img.loading = 'lazy';
      img.className = 'max-w-full w-full h-auto rounded-lg border border-border block';
      wrap.appendChild(img);
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
  function initMediaPopups() {
    document.querySelectorAll('.add-media-btn').forEach(btn => {
      const popup = btn.nextElementSibling;
      if (!popup?.classList.contains('media-popup')) return;

      btn.addEventListener('click', e => {
        e.stopPropagation();
        closeAllPopups();
        popup.classList.add('open');
      });

      popup.querySelectorAll('.media-popup-item').forEach(item => {
        item.addEventListener('click', () => {
          closeAllPopups();
          const input  = document.createElement('input');
          input.type   = 'file';
          input.accept = item.dataset.accept;
          input.addEventListener('change', async () => {
            const file = input.files?.[0];
            if (!file) return;
            const editor = btn.closest('.journal-section')?.querySelector('.block-editor');
            if (editor) await uploadAndAppend(editor, item.dataset.type, file);
          });
          input.click();
        });
      });
    });

    document.addEventListener('click', closeAllPopups);
  }

  function closeAllPopups() {
    document.querySelectorAll('.media-popup.open').forEach(p => p.classList.remove('open'));
  }

  // ── Save indicator ─────────────────────────────────────────────────────────
  function setIndicator(state) {
    const el = document.getElementById('save-indicator');
    if (!el) return;
    el.className = 'text-[11.5px] italic text-muted min-w-[68px] text-right select-none transition-colors duration-200';
    if (state === 'saving') { el.textContent = 'Guardando…'; el.classList.add('text-dim'); }
    else if (state === 'saved') { el.textContent = 'Guardado'; el.classList.add('text-success'); }
    else el.textContent = '';
  }

  // ── Toast ──────────────────────────────────────────────────────────────────
  let toastTimer = null;
  function showToast(message) {
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
    d.className       = 'block-text w-full font-lora text-[17px] font-normal leading-[1.78] text-fg outline-none border-none bg-transparent py-[2px] caret-fg break-words whitespace-pre-wrap';
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

  // ── Boot ───────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.block-editor:not(.locked)').forEach(initEditor);
    initMediaPopups();
  });

})();
