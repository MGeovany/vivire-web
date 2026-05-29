<?php

if (getAuthUser()) {
    header('Location: /');
    exit;
}

$error = '';
$name  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Completa todos los campos.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
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
            header('Location: /login?confirm=1');
            exit;
        }

        $msg = $result['error_description'] ?? $result['msg'] ?? $result['message'] ?? '';
        $error = match(true) {
            str_contains($msg, 'already registered') => 'Ya existe una cuenta con ese email.',
            str_contains($msg, 'invalid email')      => 'El email no es válido.',
            default => 'No se pudo crear la cuenta. Intenta de nuevo.',
        };
    }
}

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
      <input type="text"     name="name"     placeholder="Tu nombre"               autocomplete="name"         value="<?= htmlspecialchars($name) ?>">
      <input type="email"    name="email"    placeholder="Email"                   autocomplete="email"        value="<?= htmlspecialchars($email ?? '') ?>">
      <input type="password" name="password" placeholder="Contraseña (mín. 6 car.)" autocomplete="new-password">

      <?php if ($error): ?>
        <p class="auth-error"><?= htmlspecialchars($error) ?></p>
      <?php else: ?>
        <p class="auth-error"></p>
      <?php endif; ?>

      <button type="submit" class="auth-submit">Crear cuenta</button>
    </form>
  </div>
</div>

<?php layout_foot(); ?>
