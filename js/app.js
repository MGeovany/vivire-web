/* =========================================================================
   vivire — daily journal
   Vanilla JS, Supabase Auth + Storage, PHP API for entry CRUD.
   ========================================================================= */

'use strict';

// ─── Config ────────────────────────────────────────────────────────────────
const SUPABASE_URL      = window.SUPABASE_URL      || '';
const SUPABASE_ANON_KEY = window.SUPABASE_ANON_KEY || '';

// ─── Globals ────────────────────────────────────────────────────────────────
let supabase     = null;
let currentUser  = null;
let saveTimers   = {};     // keyed by section id
let activePopup  = null;   // currently open media popup

// Section config for "today" panel
const TODAY_SECTIONS = [
  { id: 'feelings',    label: 'Cómo me sentí',  placeholder: 'Describe cómo te sentiste hoy…' },
  { id: 'thoughts',    label: 'Qué pensé',       placeholder: 'Qué pensamientos tuviste hoy…'  },
  { id: 'reflections', label: 'Reflexiones',     placeholder: 'Reflexiona sobre el día…'       },
];

// Section config for the year panel
const YEAR_SECTIONS = [
  { id: 'year1', label: 'Cómo me sentí',  placeholder: 'Describe cómo te sentiste hoy…' },
  { id: 'year2', label: 'Qué pensé',       placeholder: 'Qué pensamientos tuviste hoy…'  },
  { id: 'year3', label: 'Reflexiones',     placeholder: 'Reflexiona sobre el día…'       },
];

// ─── Init ────────────────────────────────────────────────────────────────────
function init() {
  if (!SUPABASE_URL || !SUPABASE_ANON_KEY) {
    showToast('Configura SUPABASE_URL y SUPABASE_ANON_KEY en index.html');
    console.error('Missing Supabase config — set window.SUPABASE_URL and window.SUPABASE_ANON_KEY');
    return;
  }

  supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

  supabase.auth.onAuthStateChange((_event, session) => {
    currentUser = session?.user ?? null;
    if (currentUser) {
      showApp();
      renderApp();
    } else {
      showAuth();
    }
  });

  wireAuthUI();
  closePopupOnOutsideClick();
}

// ─── Auth ─────────────────────────────────────────────────────────────────────
function showAuth() {
  document.getElementById('auth-screen').style.display = 'flex';
  const appScreen = document.getElementById('app-screen');
  appScreen.style.display = 'none';
  appScreen.classList.remove('visible');
}

function showApp() {
  document.getElementById('auth-screen').style.display = 'none';
  const appScreen = document.getElementById('app-screen');
  appScreen.style.display = 'block';
  appScreen.classList.add('visible');
}

function wireAuthUI() {
  // Tab switching
  document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.form).classList.add('active');
      document.querySelectorAll('.auth-error').forEach(e => { e.textContent = ''; });
    });
  });

  // Sign-up
  document.getElementById('signup-form').addEventListener('submit', async e => {
    e.preventDefault();
    const name     = document.getElementById('signup-name').value.trim();
    const email    = document.getElementById('signup-email').value.trim();
    const password = document.getElementById('signup-password').value;
    const btn      = document.getElementById('signup-btn');

    if (!name || !email || !password) {
      setAuthError('Completa todos los campos.', 'signup');
      return;
    }
    if (password.length < 6) {
      setAuthError('La contraseña debe tener al menos 6 caracteres.', 'signup');
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Creando cuenta…';
    await handleSignup(name, email, password);
    btn.disabled = false;
    btn.textContent = 'Crear cuenta';
  });

  // Sign-in
  document.getElementById('signin-form').addEventListener('submit', async e => {
    e.preventDefault();
    const email    = document.getElementById('signin-email').value.trim();
    const password = document.getElementById('signin-password').value;
    const btn      = document.getElementById('signin-btn');

    if (!email || !password) {
      setAuthError('Completa todos los campos.', 'signin');
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Entrando…';
    await handleSignin(email, password);
    btn.disabled = false;
    btn.textContent = 'Entrar';
  });
}

async function handleSignup(name, email, password) {
  const { error } = await supabase.auth.signUp({
    email,
    password,
    options: { data: { name } },
  });
  if (error) {
    setAuthError(humanizeAuthError(error.message), 'signup');
  }
}

async function handleSignin(email, password) {
  const { error } = await supabase.auth.signInWithPassword({ email, password });
  if (error) {
    setAuthError(humanizeAuthError(error.message), 'signin');
  }
}

async function handleSignout() {
  await supabase.auth.signOut();
}

function humanizeAuthError(msg) {
  if (/invalid login/i.test(msg))          return 'Email o contraseña incorrectos.';
  if (/already registered/i.test(msg))     return 'Ya existe una cuenta con ese email.';
  if (/password should be/i.test(msg))     return 'La contraseña debe tener al menos 6 caracteres.';
  if (/invalid email/i.test(msg))          return 'El email no es válido.';
  if (/email not confirmed/i.test(msg))    return 'Confirma tu email antes de entrar.';
  return msg;
}

function setAuthError(msg, form = 'signin') {
  const id = form === 'signup' ? 'auth-error-signup' : 'auth-error';
  const errEl = document.getElementById(id);
  if (errEl) errEl.textContent = msg;
}

// ─── Date Helpers ─────────────────────────────────────────────────────────────
const ES_DAYS = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
const ES_MONTHS = ['enero','febrero','marzo','abril','mayo','junio',
                   'julio','agosto','septiembre','octubre','noviembre','diciembre'];

/** Format a Date as "Jueves, 29 de mayo de 2026" */
function formatDate(date) {
  const day   = ES_DAYS[date.getDay()];
  const d     = date.getDate();
  const month = ES_MONTHS[date.getMonth()];
  const year  = date.getFullYear();
  return `${day}, ${d} de ${month} de ${year}`;
}

/** Format just "Mayo 29, 2026" (for year section headings) */
function formatDateShort(date) {
  const month = ES_MONTHS[date.getMonth()];
  const cap   = month.charAt(0).toUpperCase() + month.slice(1);
  return `${cap} ${date.getDate()}, ${date.getFullYear()}`;
}

/** ISO date string YYYY-MM-DD (local time, no UTC shift) */
function toISODate(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

/** Returns true if date1 and date2 share the same month+day (any year) */
function isSameMonthDay(date1, date2) {
  return date1.getMonth() === date2.getMonth() &&
         date1.getDate()  === date2.getDate();
}

/** Returns [date+1yr, date+2yr, date+3yr] */
function getYearSections(today) {
  return [1, 2, 3].map(n => {
    const d = new Date(today);
    d.setFullYear(d.getFullYear() + n);
    return d;
  });
}

// ─── Render App ───────────────────────────────────────────────────────────────
function renderApp() {
  const container = document.getElementById('app-screen');
  container.innerHTML = '';

  const today      = new Date();
  const todayISO   = toISODate(today);
  const yearDates  = getYearSections(today);

  // ── Header
  const header = el('div', 'app-header', `
    <span class="app-logo">vivire</span>
    <div class="app-header-right">
      <span class="app-date">${formatDate(today)}</span>
      <span class="save-indicator" id="save-indicator"></span>
      <button class="signout-btn" id="signout-btn">Salir</button>
    </div>
  `);
  container.appendChild(header);
  container.appendChild(el('div', 'divider'));

  // ── Today sections
  const todayGroup = el('div', 'section-group');
  todayGroup.appendChild(el('p', 'section-group-title', 'Reflexiones de hoy'));

  TODAY_SECTIONS.forEach(sec => {
    const journalSec = buildJournalSection(sec.id, sec.label, sec.placeholder, false);
    todayGroup.appendChild(journalSec);
  });

  container.appendChild(todayGroup);

  // ── Year divider
  const yearDiv = el('div', 'years-divider', 'Misma fecha, próximos años');
  container.appendChild(yearDiv);

  // ── Year sections
  yearDates.forEach((yearDate, idx) => {
    const yearISO   = toISODate(yearDate);
    const isToday  = toISODate(yearDate) === toISODate(today);
    const isPast   = yearDate < today && !isToday;
    const isFuture = yearDate > today && !isToday;
    const sectionId = `year${idx + 1}`;

    const yearSection  = el('div', 'year-section');
    if (isFuture)  yearSection.classList.add('locked-year');
    if (isPast)    yearSection.classList.add('past-year');

    // Year header
    const yearHeader = el('div', 'year-section-date');
    yearHeader.innerHTML = `
      <span class="year-badge">${yearDate.getFullYear()}</span>
      ${formatDateShort(yearDate)}
      ${isFuture ? '<span class="section-lock-icon">🔒</span>' : ''}
    `;
    yearSection.appendChild(yearHeader);

    // One block editor per year (locked if future)
    const journalSec = buildJournalSection(sectionId, '', 'Escribe aquí…', isFuture);
    journalSec.dataset.date = yearISO;
    yearSection.appendChild(journalSec);

    container.appendChild(yearSection);
  });

  // ── Toast
  if (!document.getElementById('app-toast')) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.id = 'app-toast';
    document.body.appendChild(toast);
  }

  // Signout
  document.getElementById('signout-btn').addEventListener('click', handleSignout);

  // Load saved entries
  loadEntries(todayISO);
}

// ─── Build Journal Section ───────────────────────────────────────────────────
function buildJournalSection(sectionId, label, placeholder, locked) {
  const sec = document.createElement('div');
  sec.className = 'journal-section';
  sec.dataset.section = sectionId;

  if (label) {
    const lbl = document.createElement('div');
    lbl.className = 'section-label';
    lbl.textContent = label;
    if (locked) {
      const lockIcon = document.createElement('span');
      lockIcon.className = 'section-lock-icon';
      lockIcon.textContent = '🔒';
      lbl.appendChild(lockIcon);
    }
    sec.appendChild(lbl);
  }

  const editor = createBlockEditor(sectionId, placeholder, locked);
  sec.appendChild(editor);

  if (!locked) {
    const mediaWrap = el('div', 'add-media-wrap');
    const addBtn    = el('button', 'add-media-btn', '+');
    addBtn.title    = 'Añadir media';
    addBtn.type     = 'button';
    const popup     = buildMediaPopup(sectionId);

    addBtn.addEventListener('click', e => {
      e.stopPropagation();
      closeAllPopups();
      popup.classList.add('open');
      activePopup = popup;
    });

    mediaWrap.appendChild(addBtn);
    mediaWrap.appendChild(popup);
    sec.appendChild(mediaWrap);
  }

  return sec;
}

// ─── Block Editor ─────────────────────────────────────────────────────────────
function createBlockEditor(sectionId, placeholder, locked) {
  const wrap = document.createElement('div');
  wrap.className = 'block-editor' + (locked ? ' locked' : '');
  wrap.dataset.section = sectionId;

  // Initial text block
  const firstBlock = createTextBlock(placeholder);
  wrap.appendChild(firstBlock);

  if (!locked) {
    // Enter key → new text block
    wrap.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        const sel   = window.getSelection();
        const block = sel.anchorNode?.parentElement?.closest('.block-text');
        if (block) {
          e.preventDefault();
          const newBlock = createTextBlock(placeholder);
          block.after(newBlock);
          newBlock.focus();
          scheduleAutoSave(sectionId);
        }
      }
      // Backspace on empty block (not first) → remove and focus previous
      if (e.key === 'Backspace') {
        const sel   = window.getSelection();
        const block = sel.anchorNode?.parentElement?.closest('.block-text');
        if (block && block.textContent === '') {
          const allBlocks = Array.from(wrap.querySelectorAll('.block-text'));
          const idx = allBlocks.indexOf(block);
          if (idx > 0) {
            e.preventDefault();
            const prev = allBlocks[idx - 1];
            allBlocks[idx - 1].focus();
            // Move caret to end
            placeCursorAtEnd(prev);
            block.remove();
            scheduleAutoSave(sectionId);
          }
        }
      }
    });

    // Input → auto-save
    wrap.addEventListener('input', () => scheduleAutoSave(sectionId));

    // Paste image
    wrap.addEventListener('paste', async e => {
      const items = Array.from(e.clipboardData?.items || []);
      const imgItem = items.find(it => it.type.startsWith('image/'));
      if (imgItem) {
        e.preventDefault();
        const file = imgItem.getAsFile();
        if (file) await addMediaBlock(sectionId, 'image', file);
      }
    });
  }

  return wrap;
}

function createTextBlock(placeholder) {
  const div = document.createElement('div');
  div.className = 'block-text';
  div.contentEditable = 'true';
  div.dataset.placeholder = placeholder;
  div.dataset.type = 'text';
  return div;
}

function placeCursorAtEnd(el) {
  const range = document.createRange();
  const sel   = window.getSelection();
  range.selectNodeContents(el);
  range.collapse(false);
  sel.removeAllRanges();
  sel.addRange(range);
}

// ─── Render Blocks (from DB) ──────────────────────────────────────────────────
function renderBlocks(editorEl, blocks, placeholder) {
  // Remove all existing blocks
  editorEl.innerHTML = '';

  if (!blocks || blocks.length === 0) {
    editorEl.appendChild(createTextBlock(placeholder || 'Escribe aquí…'));
    return;
  }

  for (const block of blocks) {
    if (block.type === 'text') {
      const div = createTextBlock(placeholder || 'Escribe aquí…');
      div.textContent = block.content || '';
      editorEl.appendChild(div);
    } else {
      const mediaBlock = createMediaBlockElement(block);
      if (mediaBlock) editorEl.appendChild(mediaBlock);
    }
  }

  // Always ensure at least one text block at end for editing
  const locked = editorEl.classList.contains('locked');
  if (!locked) {
    const last = editorEl.lastElementChild;
    if (!last || !last.classList.contains('block-text')) {
      editorEl.appendChild(createTextBlock(placeholder || 'Escribe aquí…'));
    }
  }
}

// ─── Media Block Element ──────────────────────────────────────────────────────
function createMediaBlockElement(block) {
  const wrap = document.createElement('div');
  wrap.className = 'block-media';
  wrap.dataset.blockId   = block.id || '';
  wrap.dataset.blockType = block.type;

  switch (block.type) {
    case 'image': {
      const img = document.createElement('img');
      img.src = block.content;
      img.alt = block.metadata?.name || 'Imagen';
      img.loading = 'lazy';
      wrap.appendChild(img);
      break;
    }
    case 'audio': {
      const audio = document.createElement('audio');
      audio.src = block.content;
      audio.controls = true;
      wrap.appendChild(audio);
      break;
    }
    case 'video': {
      const video = document.createElement('video');
      video.src = block.content;
      video.controls = true;
      video.playsInline = true;
      wrap.appendChild(video);
      break;
    }
    case 'document': {
      const a = document.createElement('a');
      a.href = block.content;
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
      a.className = 'block-doc-card';

      const icon = el('span', 'block-doc-icon', docIcon(block.metadata?.name || ''));
      const info = document.createElement('div');
      info.className = 'block-doc-info';

      const name = el('div', 'block-doc-name', block.metadata?.name || 'Documento');
      const meta = el('div', 'block-doc-meta', block.metadata?.size
        ? formatFileSize(block.metadata.size)
        : 'Documento adjunto');

      info.appendChild(name);
      info.appendChild(meta);
      a.appendChild(icon);
      a.appendChild(info);
      wrap.appendChild(a);
      break;
    }
    default:
      return null;
  }

  return wrap;
}

// ─── Collect Blocks from DOM ──────────────────────────────────────────────────
function collectBlocks(editorEl) {
  const blocks = [];
  for (const child of editorEl.children) {
    if (child.classList.contains('block-text')) {
      const text = child.textContent.trim();
      if (text) {
        blocks.push({ id: uid(), type: 'text', content: text, metadata: {} });
      }
    } else if (child.classList.contains('block-media')) {
      const type = child.dataset.blockType;
      let content = '';
      let metadata = {};

      if (type === 'image') {
        const img = child.querySelector('img');
        content  = img?.src || '';
      } else if (type === 'audio') {
        const audio = child.querySelector('audio');
        content = audio?.src || '';
      } else if (type === 'video') {
        const video = child.querySelector('video');
        content = video?.src || '';
      } else if (type === 'document') {
        const a = child.querySelector('a');
        content = a?.href || '';
        metadata = {
          name: child.querySelector('.block-doc-name')?.textContent || '',
          size: child.querySelector('.block-doc-meta')?.dataset.size || 0,
        };
      }

      if (content) {
        blocks.push({ id: child.dataset.blockId || uid(), type, content, metadata });
      }
    }
  }
  return blocks;
}

// ─── Auto-save ────────────────────────────────────────────────────────────────
function scheduleAutoSave(sectionId) {
  if (saveTimers[sectionId]) clearTimeout(saveTimers[sectionId]);
  setIndicator('saving');
  saveTimers[sectionId] = setTimeout(() => saveSection(sectionId), 1500);
}

async function saveSection(sectionId) {
  if (!currentUser) return;

  const { data: { session } } = await supabase.auth.getSession();
  if (!session) return;

  // Find the section element — could be in today or year sections
  const sectionEl = document.querySelector(`.journal-section[data-section="${sectionId}"]`);
  if (!sectionEl) return;

  const editorEl = sectionEl.querySelector('.block-editor');
  if (!editorEl) return;

  // Determine date: today sections use today, year sections use their date
  const today   = new Date();
  let entryDate = toISODate(today);

  // If the section is inside a .year-section, grab its date from dataset
  const yearSec = sectionEl.closest('.year-section');
  if (yearSec) {
    entryDate = sectionEl.dataset.date || entryDate;
  }

  const blocks = collectBlocks(editorEl);

  try {
    const res = await fetch('/api/entries', {
      method: 'POST',
      headers: {
        'Content-Type':  'application/json',
        'Authorization': `Bearer ${session.access_token}`,
      },
      body: JSON.stringify({ entry_date: entryDate, section: sectionId, blocks }),
    });

    if (res.ok) {
      setIndicator('saved');
      setTimeout(() => setIndicator(''), 2500);
    } else {
      const err = await res.json().catch(() => ({}));
      console.error('Save failed:', err);
      setIndicator('');
    }
  } catch (err) {
    console.error('Save error:', err);
    setIndicator('');
  }
}

function setIndicator(state) {
  const el = document.getElementById('save-indicator');
  if (!el) return;
  el.className = 'save-indicator';
  if (state === 'saving') { el.textContent = 'Guardando…'; el.classList.add('saving'); }
  else if (state === 'saved') { el.textContent = 'Guardado'; el.classList.add('saved'); }
  else { el.textContent = ''; }
}

// ─── Load Entries ─────────────────────────────────────────────────────────────
async function loadEntries(dateISO) {
  if (!currentUser) return;

  const { data: { session } } = await supabase.auth.getSession();
  if (!session) return;

  try {
    const res = await fetch(`/api/entries?date=${dateISO}`, {
      headers: { 'Authorization': `Bearer ${session.access_token}` },
    });

    if (!res.ok) return;
    const entries = await res.json();
    populateSections(entries);
  } catch (err) {
    console.error('Load entries error:', err);
  }

  // Also load year section entries
  const today = new Date();
  const yearDates = getYearSections(today);
  for (let i = 0; i < yearDates.length; i++) {
    loadYearEntry(toISODate(yearDates[i]), `year${i + 1}`);
  }
}

async function loadYearEntry(dateISO, sectionId) {
  const { data: { session } } = await supabase.auth.getSession();
  if (!session) return;

  try {
    const res = await fetch(`/api/entries?date=${dateISO}`, {
      headers: { 'Authorization': `Bearer ${session.access_token}` },
    });
    if (!res.ok) return;
    const entries = await res.json();
    const entry   = entries.find(e => e.section === sectionId);
    if (entry) {
      const sectionEl = document.querySelector(`.journal-section[data-section="${sectionId}"]`);
      if (!sectionEl) return;
      const editorEl  = sectionEl.querySelector('.block-editor');
      if (!editorEl)  return;
      renderBlocks(editorEl, entry.blocks, 'Escribe aquí…');
    }
  } catch (err) {
    console.error('Load year entry error:', err);
  }
}

function populateSections(entries) {
  const SECTION_PLACEHOLDERS = {
    feelings:    'Describe cómo te sentiste hoy…',
    thoughts:    'Qué pensamientos tuviste hoy…',
    reflections: 'Reflexiona sobre el día…',
  };

  for (const entry of entries) {
    const sectionId = entry.section;
    const sectionEl = document.querySelector(`.journal-section[data-section="${sectionId}"]`);
    if (!sectionEl) continue;

    const editorEl = sectionEl.querySelector('.block-editor');
    if (!editorEl) continue;

    const placeholder = SECTION_PLACEHOLDERS[sectionId] || 'Escribe aquí…';
    renderBlocks(editorEl, entry.blocks, placeholder);
  }
}

// ─── Media Upload ─────────────────────────────────────────────────────────────
async function uploadFile(file, sectionId) {
  if (!currentUser) return null;

  const ext      = file.name.split('.').pop();
  const filename = `${currentUser.id}/${sectionId}/${uid()}.${ext}`;

  const { data, error } = await supabase.storage
    .from('journal-media')
    .upload(filename, file, { cacheControl: '3600', upsert: false });

  if (error) {
    console.error('Upload error:', error);
    showToast('Error al subir archivo');
    return null;
  }

  const { data: urlData } = supabase.storage
    .from('journal-media')
    .getPublicUrl(data.path);

  return urlData?.publicUrl || null;
}

async function addMediaBlock(sectionId, type, file) {
  const sectionEl = document.querySelector(`.journal-section[data-section="${sectionId}"]`);
  if (!sectionEl) return;

  const editorEl = sectionEl.querySelector('.block-editor');
  if (!editorEl)  return;

  // Show uploading placeholder
  const uploadingEl = el('div', 'block-media-uploading');
  uploadingEl.innerHTML = '<div class="uploading-spinner"></div> Subiendo…';
  editorEl.appendChild(uploadingEl);

  const url = await uploadFile(file, sectionId);
  uploadingEl.remove();

  if (!url) return;

  const block = {
    id: uid(),
    type,
    content: url,
    metadata: { name: file.name, size: file.size },
  };

  const blockEl = createMediaBlockElement(block);
  if (blockEl) {
    editorEl.appendChild(blockEl);
    scheduleAutoSave(sectionId);
  }
}

// ─── Media Popup ──────────────────────────────────────────────────────────────
function buildMediaPopup(sectionId) {
  const popup = document.createElement('div');
  popup.className = 'media-popup';

  const items = [
    { type: 'image',    icon: '🖼',  label: 'Imagen',    accept: 'image/*'  },
    { type: 'audio',    icon: '🎵',  label: 'Audio',     accept: 'audio/*'  },
    { type: 'video',    icon: '🎬',  label: 'Video',     accept: 'video/*'  },
    { type: 'document', icon: '📄',  label: 'Documento', accept: '*/*'      },
  ];

  for (const item of items) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'media-popup-item';
    btn.innerHTML = `<span class="media-popup-icon">${item.icon}</span>${item.label}`;

    btn.addEventListener('click', () => {
      closeAllPopups();
      const input    = document.createElement('input');
      input.type     = 'file';
      input.accept   = item.accept;
      input.multiple = false;
      input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (file) await addMediaBlock(sectionId, item.type, file);
      });
      input.click();
    });

    popup.appendChild(btn);
  }

  return popup;
}

function closeAllPopups() {
  document.querySelectorAll('.media-popup.open').forEach(p => p.classList.remove('open'));
  activePopup = null;
}

function closePopupOnOutsideClick() {
  document.addEventListener('click', () => closeAllPopups());
}

// ─── Toast ────────────────────────────────────────────────────────────────────
function showToast(msg) {
  let toast = document.getElementById('app-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'toast';
    toast.id = 'app-toast';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

// ─── Utility ──────────────────────────────────────────────────────────────────
function el(tag, className, html) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (html !== undefined) node.innerHTML = html;
  return node;
}

function uid() {
  return Math.random().toString(36).slice(2, 10) + Date.now().toString(36);
}

function formatFileSize(bytes) {
  if (!bytes) return '';
  if (bytes < 1024)        return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function docIcon(name) {
  const ext = name.split('.').pop().toLowerCase();
  if (['pdf'].includes(ext))                     return '📕';
  if (['doc','docx'].includes(ext))              return '📝';
  if (['xls','xlsx','csv'].includes(ext))        return '📊';
  if (['ppt','pptx'].includes(ext))              return '📋';
  if (['zip','rar','7z','tar','gz'].includes(ext)) return '🗜';
  return '📄';
}

// ─── Boot ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', init);
