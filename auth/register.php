<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/supabase.php';
require_once __DIR__ . '/../templates/layout.php';

if (getAuthUser()) { header('Location: /'); exit; }

// ── POST: create account, then ALWAYS redirect (PRG pattern) ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        header('Location: /register?e=fields&name=' . urlencode($name) . '&email=' . urlencode($email));
        exit;
    }
    if (strlen($password) < 6) {
        header('Location: /register?e=short&name=' . urlencode($name) . '&email=' . urlencode($email));
        exit;
    }

    $result = supabaseAuthRequest('POST', '/signup', [
        'email'    => $email,
        'password' => $password,
        'data'     => ['name' => $name],
    ]);

    if (isset($result['access_token'])) {
        setAuthCookies($result);
        header('Location: /');
        exit;
    }
    if (isset($result['id'])) {
        // Email confirmation required
        header('Location: /login?confirm=1');
        exit;
    }

    $msg  = $result['error_description'] ?? $result['msg'] ?? $result['message'] ?? '';
    $code = match(true) {
        str_contains($msg, 'already registered') => 'taken',
        str_contains($msg, 'invalid email')      => 'email',
        default                                   => 'server',
    };
    header('Location: /register?e=' . $code . '&name=' . urlencode($name) . '&email=' . urlencode($email));
    exit;
}

// ── GET: render form ──────────────────────────────────────────────────────────
$error = match($_GET['e'] ?? '') {
    'fields' => 'Completa todos los campos.',
    'short'  => 'La contraseña debe tener al menos 6 caracteres.',
    'taken'  => 'Ya existe una cuenta con ese email.',
    'email'  => 'El email no es válido.',
    'server' => 'No se pudo crear la cuenta. Intenta de nuevo.',
    default  => '',
};
$name  = htmlspecialchars($_GET['name']  ?? '');
$email = htmlspecialchars($_GET['email'] ?? '');

layout_head('vivire — Crear cuenta');
?>

<div id="auth-screen">
  <div class="auth-card">
    <span class="auth-logo">vivire</span>

    <div class="auth-tabs">
      <a class="auth-tab"        href="/login">Entrar</a>
      <a class="auth-tab active" href="/register">Crear cuenta</a>
    </div>

    <form method="POST" action="/register" class="auth-form active" novalidate>
      <input type="text"     name="name"     placeholder="Tu nombre"               autocomplete="name"
             value="<?= $name ?>">
      <input type="email"    name="email"    placeholder="Email"                   autocomplete="email"
             value="<?= $email ?>">
      <input type="password" name="password" placeholder="Contraseña (mín. 6 car.)" autocomplete="new-password">

      <p class="auth-error"><?= htmlspecialchars($error) ?></p>

      <button type="submit" class="auth-submit">Crear cuenta</button>
    </form>
  </div>
</div>

<?php layout_foot(); ?>
