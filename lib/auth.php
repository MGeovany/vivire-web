<?php

// ── JWT ────────────────────────────────────────────────────────────────────────

function base64url_decode(string $input): string {
    $rem = strlen($input) % 4;
    if ($rem) $input .= str_repeat('=', 4 - $rem);
    return base64_decode(strtr($input, '-_', '+/'));
}

function verifyJWT(string $token, string $secret): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$hB64, $pB64, $sigB64] = $parts;

    $expected = hash_hmac('sha256', "$hB64.$pB64", $secret, true);
    if (!hash_equals($expected, base64url_decode($sigB64))) return null;

    $header = json_decode(base64url_decode($hB64), true);
    if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') return null;

    $payload = json_decode(base64url_decode($pB64), true);
    if (!is_array($payload)) return null;

    if (isset($payload['exp']) && $payload['exp'] < time()) return null;

    return $payload;
}

// ── Cookie helpers ────────────────────────────────────────────────────────────

function setAuthCookies(array $authData): void {
    $base = ['path' => '/', 'httponly' => true, 'samesite' => 'Strict'];
    // HTTPS in production; allow HTTP locally
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    setcookie(AUTH_COOKIE, $authData['access_token'], $base + [
        'expires' => time() + ($authData['expires_in'] ?? 3600),
        'secure'  => $secure,
    ]);
    setcookie(REFRESH_COOKIE, $authData['refresh_token'], $base + [
        'expires' => time() + 60 * 60 * 24 * 365,
        'secure'  => $secure,
    ]);
}

function clearAuthCookies(): void {
    $past = ['expires' => time() - 3600, 'path' => '/'];
    setcookie(AUTH_COOKIE,   '', $past);
    setcookie(REFRESH_COOKIE,'', $past);
}

// ── Token resolution (with auto-refresh) ─────────────────────────────────────

function getValidToken(): ?string {
    $token = $_COOKIE[AUTH_COOKIE] ?? null;

    if ($token) {
        $payload = verifyJWT($token, SUPABASE_JWT_SECRET);
        if ($payload !== null) return $token;
    }

    // Access token missing or expired — try refresh
    $refresh = $_COOKIE[REFRESH_COOKIE] ?? null;
    if (!$refresh) return null;

    $result = supabaseAuthRequest('POST', '/token?grant_type=refresh_token', [
        'refresh_token' => $refresh,
    ]);
    if ($result && isset($result['access_token'])) {
        setAuthCookies($result);
        return $result['access_token'];
    }

    return null;
}

function getAuthUser(): ?array {
    $token = getValidToken();
    if (!$token) return null;

    $payload = verifyJWT($token, SUPABASE_JWT_SECRET);
    if ($payload !== null) {
        return $payload;
    }

    // ES256 / newer Supabase JWTs — verify via Auth API
    $user = supabaseGetUser($token);
    if (!$user) {
        return null;
    }

    return [
        'sub'   => $user['id'],
        'email' => $user['email'] ?? '',
    ];
}

/**
 * Require authentication. Redirects to /login if not authenticated.
 * Returns the JWT payload (includes 'sub' = user UUID).
 */
function requireAuth(): array {
    $user = getAuthUser();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    return $user;
}
