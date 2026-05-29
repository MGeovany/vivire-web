<?php

/**
 * CORS headers — call at the top of every API endpoint.
 */
function corsHeaders(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Emit a JSON response and stop execution.
 */
function jsonResponse(mixed $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Decode a base64url-encoded string (RFC 4648 §5).
 */
function base64url_decode(string $input): string {
    $remainder = strlen($input) % 4;
    if ($remainder !== 0) {
        $input .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

/**
 * Verify a Supabase HS256 JWT and return its payload array, or null on failure.
 *
 * @param string $token  Raw JWT string (without "Bearer " prefix).
 * @param string $secret The SUPABASE_JWT_SECRET value.
 * @return array<string,mixed>|null
 */
function verifyJWT(string $token, string $secret): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$headerB64, $payloadB64, $signatureB64] = $parts;

    // Verify signature
    $signingInput   = $headerB64 . '.' . $payloadB64;
    $expectedSig    = hash_hmac('sha256', $signingInput, $secret, true);
    $providedSig    = base64url_decode($signatureB64);

    if (!hash_equals($expectedSig, $providedSig)) {
        return null;
    }

    // Decode header to confirm algorithm
    $header = json_decode(base64url_decode($headerB64), true);
    if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') {
        return null;
    }

    // Decode payload
    $payload = json_decode(base64url_decode($payloadB64), true);
    if (!is_array($payload)) {
        return null;
    }

    // Check expiry
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return null;
    }

    return $payload;
}

/**
 * Extract and verify the Bearer token from the Authorization header.
 * Returns the user UUID on success; sends 401 and exits on failure.
 */
function getAuthUser(): string {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!str_starts_with($authHeader, 'Bearer ')) {
        jsonResponse(['error' => 'Missing or invalid Authorization header'], 401);
    }

    $token  = substr($authHeader, 7);
    $secret = $_ENV['SUPABASE_JWT_SECRET'] ?? getenv('SUPABASE_JWT_SECRET') ?? '';

    if ($secret === '') {
        jsonResponse(['error' => 'Server misconfiguration: missing JWT secret'], 500);
    }

    $payload = verifyJWT($token, $secret);

    if ($payload === null) {
        jsonResponse(['error' => 'Invalid or expired token'], 401);
    }

    $userId = $payload['sub'] ?? '';
    if ($userId === '') {
        jsonResponse(['error' => 'Token missing sub claim'], 401);
    }

    return $userId;
}

/**
 * Make a cURL request to the Supabase REST API.
 *
 * @param string $method   HTTP method (GET, POST, PATCH, DELETE)
 * @param string $path     Path relative to /rest/v1 (e.g. '/entries')
 * @param array  $query    Query-string parameters
 * @param array|null $body Request body (will be JSON-encoded)
 * @param array  $extraHeaders Additional headers to merge
 * @return array{status: int, body: mixed}
 */
function supabaseRequest(
    string $method,
    string $path,
    array  $query       = [],
    ?array $body        = null,
    array  $extraHeaders = []
): array {
    $baseUrl    = rtrim($_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?? '', '/');
    $serviceKey = $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? getenv('SUPABASE_SERVICE_ROLE_KEY') ?? '';

    if ($baseUrl === '' || $serviceKey === '') {
        jsonResponse(['error' => 'Server misconfiguration: missing Supabase credentials'], 500);
    }

    $url = $baseUrl . '/rest/v1' . $path;
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $headers = array_merge([
        'apikey: '        . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ], $extraHeaders);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $response   = curl_exec($ch);
    $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError !== '') {
        jsonResponse(['error' => 'Upstream request failed: ' . $curlError], 502);
    }

    return [
        'status' => $httpStatus,
        'body'   => json_decode($response, true),
    ];
}
