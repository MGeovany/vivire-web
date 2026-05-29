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

<div class="flex items-center justify-center min-h-screen p-6">
  <div class="w-full max-w-[380px]">
    <span class="font-serif text-[28px] font-normal tracking-[-0.3px] text-fg block mb-10">vivire</span>

    <div class="flex mb-7 border-b border-border">
      <a href="/login" class="text-[13.5px] font-normal text-subtle pb-2 mr-[22px] border-b-[1.5px] border-transparent -mb-px transition-[color,border-color] duration-150 no-underline inline-block hover:text-fg">Entrar</a>
      <a href="/register" class="text-[13.5px] font-normal text-subtle pb-2 mr-[22px] border-b-[1.5px] border-fg -mb-px transition-[color,border-color] duration-150 no-underline inline-block hover:text-fg">Crear cuenta</a>
    </div>

    <form method="POST" action="/register" class="flex flex-col gap-3" novalidate>
      <input type="text"     name="name"     placeholder="Tu nombre"               autocomplete="name"
             value="<?= $name ?>"
             class="w-full px-3.5 py-[11px] text-sm text-fg bg-white border border-border rounded-lg outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-muted focus:border-fg focus:shadow-[0_0_0_3px_rgba(28,27,25,0.06)] appearance-none">
      <input type="email"    name="email"    placeholder="Email"                   autocomplete="email"
             value="<?= $email ?>"
             class="w-full px-3.5 py-[11px] text-sm text-fg bg-white border border-border rounded-lg outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-muted focus:border-fg focus:shadow-[0_0_0_3px_rgba(28,27,25,0.06)] appearance-none">
      <input type="password" name="password" placeholder="Contraseña (mín. 6 car.)" autocomplete="new-password"
             class="w-full px-3.5 py-[11px] text-sm text-fg bg-white border border-border rounded-lg outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-muted focus:border-fg focus:shadow-[0_0_0_3px_rgba(28,27,25,0.06)] appearance-none">

      <p class="text-[12.5px] text-error min-h-4"><?= $error ?></p>

      <button type="submit" class="mt-1 px-3.5 py-[11px] text-sm font-medium text-bg bg-fg rounded-lg transition-opacity duration-150 cursor-pointer tracking-[0.01em] hover:opacity-[0.82] disabled:opacity-35 disabled:cursor-not-allowed">Crear cuenta</button>
    </form>
  </div>
</div>

<?php layout_foot(); ?>
