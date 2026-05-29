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
<script>
(function(){'use strict';var s=document.getElementById('app-toast'),t;function o(m){if(!s)return;s.textContent=String(m||'').trim()||'Error';s.classList.add('show');clearTimeout(t);t=setTimeout(function(){s.classList.remove('show')},3500)}function r(e){var f=e.querySelector('button[type=submit]');if(!f||f.dataset.loading)return;f.dataset.loading='true';f.disabled=true;f.innerHTML='<span class="inline-flex items-center gap-2"><svg class="animate-spin -ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>'+f.textContent+'</span>';f.style.pointerEvents='none';o('Enviando\u2026')}document.querySelectorAll('form').forEach(function(f){f.addEventListener('submit',function(){r(f)})});window.showToast=o})();
</script>
</body>
</html>
<?php }
