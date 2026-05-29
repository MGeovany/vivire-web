<?php
function layout_head(string $title = 'vivire'): void { ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📓</text></svg>">

  <!-- Fonts — loaded as <link> (faster and more reliable than @import) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&family=Lora:ital,wght@0,400;0,500;1,400;1,500&display=swap">

  <link rel="stylesheet" href="/css/tailwind.css">
  <link rel="stylesheet" href="/css/app.css">
</head>
<body class="font-sans bg-bg text-fg leading-relaxed min-h-screen overflow-x-hidden antialiased">
<?php }

function layout_foot(): void { ?>
<div class="toast fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-[10px] bg-fg text-bg text-[13px] px-5 py-[9px] rounded-full opacity-0 transition-[opacity,transform] duration-200 pointer-events-none z-[1000] whitespace-nowrap tracking-[0.01em]" id="app-toast"></div>
<script>
(function(){'use strict';var el=document.getElementById('app-toast'),timer;function toast(m){if(!el)return;el.textContent=(m||'').trim()||'Error';el.classList.add('show');clearTimeout(timer);timer=setTimeout(function(){el.classList.remove('show')},3500)}function loadingBtn(f){if(!f||f.dataset.loading)return;f.dataset.loading='true';f.disabled=true;var t=f.textContent;f.innerHTML='<span class="inline-flex items-center gap-2"><svg class="animate-spin -ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>'+t+'</span>'}document.querySelectorAll('form').forEach(function(f){f.addEventListener('submit',function(){var btn=f.querySelector('button[type=submit]');loadingBtn(btn);toast('Enviando…')})});window.showToast=toast})();
</script>
</body>
</html>
<?php }
