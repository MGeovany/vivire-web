<?php

if (getAuthUser()) {
    header('Location: /');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Completa todos los campos.';
    } else {
        $result = supabaseAuthRequest('POST', '/token?grant_type=password', [
            'email'    => $email,
            'password' => $password,
        ]);

        if (isset($result['access_token'])) {
            setAuthCookies($result);
            header('Location: /');
            exit;
        }

        $msg = $result['error_description'] ?? $result['msg'] ?? '';
        $error = match(true) {
            str_contains($msg, 'Invalid login') => 'Email o contraseña incorrectos.',
            str_contains($msg, 'Email not confirmed') => 'Confirma tu email antes de entrar.',
            default => 'No se pudo iniciar sesión. Intenta de nuevo.',
        };
    }
}

layout_head('vivire — Entrar');
?>

<div id="auth-screen">
  <div class="auth-card">
    <span class="auth-logo">vivire</span>

    <div class="auth-tabs">
      <a class="auth-tab active" href="/login">Entrar</a>
      <a class="auth-tab"        href="/register">Crear cuenta</a>
    </div>

    <form method="POST" action="/login" class="auth-form active" novalidate>
      <input type="email"    name="email"    placeholder="Email"       autocomplete="email"            value="<?= htmlspecialchars($email ?? '') ?>">
      <input type="password" name="password" placeholder="Contraseña"  autocomplete="current-password">

      <?php if ($error): ?>
        <p class="auth-error"><?= htmlspecialchars($error) ?></p>
      <?php else: ?>
        <p class="auth-error"></p>
      <?php endif; ?>

      <button type="submit" class="auth-submit">Entrar</button>
    </form>
  </div>
</div>

<?php layout_foot(); ?>
