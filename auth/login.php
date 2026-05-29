<?php
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
    'callback'    => 'No se pudo completar la confirmación. Intenta entrar con tu contraseña.',
    'server'      => 'No se pudo iniciar sesión. Intenta de nuevo.',
    default       => '',
};
$confirm = isset($_GET['confirm']);
$email   = htmlspecialchars($_GET['email'] ?? '');

layout_head('vivire — Entrar');
?>

<div class="flex items-center justify-center min-h-screen p-6">
  <div class="w-full max-w-[380px]">
    <span class="font-serif text-[28px] font-normal tracking-[-0.3px] text-fg block mb-10">vivire</span>

    <div class="flex mb-7 border-b border-border">
      <a href="/login" class="text-[13.5px] font-normal text-subtle pb-2 mr-[22px] border-b-[1.5px] border-fg -mb-px transition-[color,border-color] duration-150 no-underline inline-block hover:text-fg">Entrar</a>
      <a href="/register" class="text-[13.5px] font-normal text-subtle pb-2 mr-[22px] border-b-[1.5px] border-transparent -mb-px transition-[color,border-color] duration-150 no-underline inline-block hover:text-fg">Crear cuenta</a>
    </div>

    <?php if ($confirm): ?>
      <p class="text-[12.5px] text-success min-h-4 mb-3">Revisa tu email para confirmar la cuenta.</p>
    <?php endif; ?>

    <form method="POST" action="/login" class="flex flex-col gap-3" novalidate>
      <input type="email"    name="email"    placeholder="Email"       autocomplete="email"
             value="<?= $email ?>"
             class="w-full px-3.5 py-[11px] text-sm text-fg bg-white border border-border rounded-lg outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-muted focus:border-fg focus:shadow-[0_0_0_3px_rgba(28,27,25,0.06)] appearance-none">
      <input type="password" name="password" placeholder="Contraseña"  autocomplete="current-password"
             class="w-full px-3.5 py-[11px] text-sm text-fg bg-white border border-border rounded-lg outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-muted focus:border-fg focus:shadow-[0_0_0_3px_rgba(28,27,25,0.06)] appearance-none">

      <p class="text-[12.5px] text-error min-h-4"><?= htmlspecialchars($error) ?></p>

      <button type="submit" class="mt-1 px-3.5 py-[11px] text-sm font-medium text-bg bg-fg rounded-lg transition-opacity duration-150 cursor-pointer tracking-[0.01em] hover:opacity-[0.82] disabled:opacity-35 disabled:cursor-not-allowed">Entrar</button>
    </form>
  </div>
</div>

<?php layout_foot(); ?>
