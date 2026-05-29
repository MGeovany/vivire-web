<?php
require_once __DIR__ . '/../config.php';

/**
 * Call the Supabase REST API (PostgREST).
 * Uses the service-role key — server-side only.
 */
function supabaseRequest(
    string $method,
    string $path,
    array  $query        = [],
    ?array $body         = null,
    array  $extraHeaders = []
): array {
    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1' . $path;
    if ($query) $url .= '?' . http_build_query($query);

    $headers = array_merge([
        'apikey: '           . SUPABASE_SERVICE_ROLE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
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
    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'body' => json_decode($raw, true)];
}

/**
 * Call the Supabase Auth API.
 * Uses the anon key — public auth endpoints.
 */
function supabaseAuthRequest(string $method, string $path, array $body = []): ?array {
    $url = rtrim(SUPABASE_URL, '/') . '/auth/v1' . $path;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . SUPABASE_ANON_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_TIMEOUT    => 15,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    return json_decode($raw, true);
}

/**
 * Upload a file to Supabase Storage.
 */
function supabaseStorageUpload(string $path, string $fileData, string $mimeType): ?string {
    $url = rtrim(SUPABASE_URL, '/') . '/storage/v1/object/' . STORAGE_BUCKET . '/' . $path;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
            'Content-Type: ' . $mimeType,
            'x-upsert: false',
        ],
        CURLOPT_POSTFIELDS => $fileData,
        CURLOPT_TIMEOUT    => 60,
    ]);
    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status >= 400) return null;

    $publicUrl = rtrim(SUPABASE_URL, '/') . '/storage/v1/object/public/' . STORAGE_BUCKET . '/' . $path;
    return $publicUrl;
}
