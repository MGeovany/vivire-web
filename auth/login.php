<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/supabase.php';
require_once __DIR__ . '/../templates/layout.php';

// Already authenticated
if (getAuthUser()) { header('Location: /'); exit; }

// ── POST: authenticate, then ALWAYS redirect (PRG pattern) ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        header('Location: /login?e=fields&email=' . urlencode($email));
        exit;
    }

    $result = supabaseAuthRequest('POST', '/token?grant_type=password', [
        'email'    => $email,
        'password' => $password,
    ]);

    if (isset($result['access_token'])) {
        setAuthCookies($result);
        header('Location: /');
        exit;
    }

    $msg  = $result['error_description'] ?? $result['msg'] ?? '';
    $code = match(true) {
        str_contains($msg, 'Invalid login')       => 'invalid',
        str_contains($msg, 'Email not confirmed') => 'unconfirmed',
        default                                   => 'server',
    };
    header('Location: /login?e=' . $code . '&email=' . urlencode($email));
    exit;
}

// ── GET: render form ──────────────────────────────────────────────────────────
$error = match($_GET['e'] ?? '') {
    'fields'      => 'Completa todos los campos.',
    'invalid'     => 'Email o contraseña incorrectos.',
    'unconfirmed' => 'Confirma tu email antes de entrar.',
    'server'      => 'No se pudo iniciar sesión. Intenta de nuevo.',
    default       => '',
};
$confirm = isset($_GET['confirm']);
$email   = htmlspecialchars($_GET['email'] ?? '');

layout_head('vivire — Entrar');
?>

<div id="auth-screen">
  <div class="auth-card">
    <span class="auth-logo">vivire</span>

    <div class="auth-tabs">
      <a class="auth-tab active" href="/login">Entrar</a>
      <a class="auth-tab"        href="/register">Crear cuenta</a>
    </div>

    <?php if ($confirm): ?>
      <p class="auth-error" style="color:#52A36A">Revisa tu email para confirmar la cuenta.</p>
    <?php endif; ?>

    <form method="POST" action="/login" class="auth-form active" novalidate>
      <input type="email"    name="email"    placeholder="Email"       autocomplete="email"
             value="<?= $email ?>">
      <input type="password" name="password" placeholder="Contraseña"  autocomplete="current-password">

      <p class="auth-error"><?= htmlspecialchars($error) ?></p>

      <button type="submit" class="auth-submit">Entrar</button>
    </form>
  </div>
</div>

<?php layout_foot(); ?>
