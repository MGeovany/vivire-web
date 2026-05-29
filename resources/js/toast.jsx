import React from 'react';
import { createRoot } from 'react-dom/client';
import { Toaster, toast } from 'sonner';

let loadingId = null;
let loadingDepth = 0;

function showToast(message, type = 'info') {
  const msg = String(message || '').trim() || 'Algo salió mal';

  switch (type) {
    case 'error':
      toast.error(msg);
      break;
    case 'success':
      toast.success(msg);
      break;
    case 'loading':
      return toast.loading(msg);
    default:
      toast.message(msg);
  }
}

function hideToast() {
  toast.dismiss();
}

function showLoading() {
  loadingDepth += 1;
  if (loadingId === null) {
    loadingId = toast.loading('Cargando…');
  }
}

function hideLoading() {
  loadingDepth = Math.max(0, loadingDepth - 1);
  if (loadingDepth === 0 && loadingId !== null) {
    toast.dismiss(loadingId);
    loadingId = null;
  }
}

function showFlashMessages() {
  const flashEl = document.getElementById('vivire-flash');
  if (!flashEl) return;

  try {
    const data = JSON.parse(flashEl.textContent);
    if (data.status) showToast(data.status, 'success');
    if (data.error) showToast(data.error, 'error');
  } catch {
    /* ignore malformed flash payload */
  }
}

function wireLivewire() {
  if (!window.Livewire || window.__vivireToastWired) return;
  window.__vivireToastWired = true;

  Livewire.hook('commit', ({ succeed, fail }) => {
    showLoading();
    succeed(() => hideLoading());
    fail(() => {
      hideLoading();
      showToast('No se pudo completar la acción. Intenta de nuevo.', 'error');
    });
  });
}

function init() {
  let mount = document.getElementById('vivire-sonner');
  if (!mount) {
    mount = document.createElement('div');
    mount.id = 'vivire-sonner';
    document.body.appendChild(mount);
  }

  createRoot(mount).render(
    <Toaster
      position="bottom-center"
      expand={false}
      richColors={false}
      closeButton={false}
      duration={3500}
      gap={10}
      toastOptions={{
        classNames: {
          toast: 'vivire-toast',
          error: 'vivire-toast-error',
          success: 'vivire-toast-success',
          loading: 'vivire-toast-loading',
        },
      }}
    />,
  );

  showFlashMessages();
  wireLivewire();
}

window.showToast = showToast;
window.hideToast = hideToast;
window.showLoading = showLoading;
window.hideLoading = hideLoading;

document.addEventListener('livewire:init', wireLivewire);
document.addEventListener('livewire:navigate', showLoading);
document.addEventListener('livewire:navigated', hideLoading);

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
