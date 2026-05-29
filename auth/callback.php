<?php

layout_head('vivire — Confirmando…');
?>

<div id="auth-screen">
  <div class="auth-card">
    <span class="auth-logo">vivire</span>
    <p class="auth-error" id="callback-msg" style="color:#787774">Confirmando tu cuenta…</p>
  </div>
</div>

<script>
(function () {
  var hash = window.location.hash.replace(/^#/, '');
  if (!hash) {
    window.location.replace('/login?e=callback');
    return;
  }

  var params = new URLSearchParams(hash);
  var access  = params.get('access_token');
  var refresh = params.get('refresh_token');
  var expires = parseInt(params.get('expires_in') || '3600', 10);

  if (!access || !refresh) {
    window.location.replace('/login?e=callback');
    return;
  }

  fetch('/auth/session', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({
      access_token: access,
      refresh_token: refresh,
      expires_in: expires
    })
  })
    .then(function (r) {
      if (r.ok) {
        window.location.replace('/');
        return;
      }
      window.location.replace('/login?e=callback');
    })
    .catch(function () {
      window.location.replace('/login?e=callback');
    });
})();
</script>

<?php layout_foot(); ?>
