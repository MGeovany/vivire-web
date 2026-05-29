{{-- Global toast + top loading bar. Include AFTER @livewireScripts. --}}
<div id="app-loading" aria-hidden="true"></div>
<div class="toast fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-3 bg-fg text-bg text-[13px] font-normal px-5 py-2.5 rounded-full opacity-0 pointer-events-none z-[1000] whitespace-nowrap" id="app-toast"></div>

<script>
(function () {
  // ── Toast ────────────────────────────────────────────────────────────────
  var toastEl, toastTimer;
  window.showToast = function (message, type) {
    toastEl = toastEl || document.getElementById('app-toast');
    if (!toastEl) return;
    type = type || 'info';
    toastEl.className = toastEl.className.replace(/\btoast-(error|success|info|loading)\b/g, '').trim() + ' toast-' + type;
    toastEl.textContent = String(message || '').trim() || 'Algo salió mal';
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    if (type !== 'loading') {
      toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 3500);
    }
  };
  window.hideToast = function () {
    if (toastEl) toastEl.classList.remove('show');
  };

  // ── Top loading bar ────────────────────────────────────────────────────────
  var bar, depth = 0, trickle;
  function barEl() { return bar || (bar = document.getElementById('app-loading')); }
  window.showLoading = function () {
    var el = barEl(); if (!el) return;
    depth++;
    el.classList.add('active');
    el.style.width = '12%';
    clearInterval(trickle);
    var w = 12;
    trickle = setInterval(function () { w = Math.min(w + (90 - w) * 0.18, 90); el.style.width = w + '%'; }, 220);
  };
  window.hideLoading = function () {
    var el = barEl(); if (!el) return;
    depth = Math.max(0, depth - 1);
    if (depth > 0) return;
    clearInterval(trickle);
    el.style.width = '100%';
    setTimeout(function () {
      el.classList.remove('active');
      setTimeout(function () { el.style.width = '0%'; }, 250);
    }, 200);
  };

  // ── Livewire integration (loading bar + error toast on failure) ─────────────
  function wireLivewire() {
    if (!window.Livewire || window.__vivireWired) return;
    window.__vivireWired = true;

    Livewire.hook('commit', function (payload) {
      var succeed = payload.succeed, fail = payload.fail;
      window.showLoading();
      succeed(function () { window.hideLoading(); });
      fail(function () {
        window.hideLoading();
        window.showToast('No se pudo completar la acción. Intenta de nuevo.', 'error');
      });
    });
  }
  document.addEventListener('livewire:init', wireLivewire);
  wireLivewire();

  // SPA navigation progress
  document.addEventListener('livewire:navigate',  function () { window.showLoading(); });
  document.addEventListener('livewire:navigated', function () { window.hideLoading(); });

  // ── Flash messages → toast ──────────────────────────────────────────────────
  @if (session('status'))
    window.showToast(@json(session('status')), 'success');
  @endif
  @if (session('error'))
    window.showToast(@json(session('error')), 'error');
  @endif

  // ── Password show/hide toggle (delegated, survives SPA nav) ─────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.password-toggle');
    if (!btn) return;
    e.preventDefault();
    var field = btn.closest('.password-field');
    var input = field && field.querySelector('input');
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    btn.dataset.shown = show ? '1' : '0';
    var icons = btn.querySelectorAll('svg');
    if (icons.length === 2) { icons[0].style.display = show ? 'none' : ''; icons[1].style.display = show ? '' : 'none'; }
  });
})();
</script>
