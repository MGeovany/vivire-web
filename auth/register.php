<?php
if (getAuthUser()) { header('Location: /'); exit; }

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

    $redirectTo = rtrim(APP_URL, '/') . '/auth/callback';

    $result = supabaseAuthRequest('POST', '/signup?redirect_to=' . urlencode($redirectTo), [
        'email'    => $email,
        'password' => $password,
        'data'     => ['name' => $name],
    ]);

    // Log response in dev for debugging
    error_log('[vivire] signup response: ' . json_encode($result));

    // Auto-confirmed signup — access_token at root level
    if (!empty($result['access_token'])) {
        setAuthCookies($result);
        header('Location: /');
        exit;
    }

    // Email confirmation required — user object returned without session
    $userId = $result['id'] ?? $result['user']['id'] ?? null;
    if ($userId) {
        header('Location: /login?confirm=1');
        exit;
    }

    // Error
    $msg  = $result['error_description'] ?? $result['msg'] ?? $result['message'] ?? $result['error'] ?? '';
    error_log('[vivire] signup error: ' . $msg);

    $code = match(true) {
        str_contains((string)$msg, 'already registered')   => 'taken',
        str_contains((string)$msg, 'User already registered') => 'taken',
        str_contains((string)$msg, 'invalid email')        => 'email',
        default                                             => 'server',
    };

    // In dev, surface raw error via URL so it shows in the form
    $debugParam = IS_DEV ? '&dbg=' . urlencode(substr((string)$msg, 0, 120)) : '';
    header('Location: /register?e=' . $code . $debugParam . '&name=' . urlencode($name) . '&email=' . urlencode($email));
    exit;
}

// GET ─────────────────────────────────────────────────────────────────────────
$error = match($_GET['e'] ?? '') {
    'fields' => 'Completa todos los campos.',
    'short'  => 'La contraseña debe tener al menos 6 caracteres.',
    'taken'  => 'Ya existe una cuenta con ese email.',
    'email'  => 'El email no es válido.',
    'server' => 'No se pudo crear la cuenta. Intenta de nuevo.',
    default  => '',
};
// Dev: show raw Supabase message
if (IS_DEV && !empty($_GET['dbg'])) {
    $error = '[dev] ' . htmlspecialchars(urldecode($_GET['dbg']));
}

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

      <p class="auth-error"><?= $error ?></p>

      <button type="submit" class="auth-submit">Crear cuenta</button>
    </form>
  </div>
</div>

<?php layout_foot(); ?>
