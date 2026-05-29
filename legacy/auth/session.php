<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid body']);
    exit;
}

$access  = $data['access_token']  ?? '';
$refresh = $data['refresh_token'] ?? '';
$expires = (int) ($data['expires_in'] ?? 3600);

if (!$access || !$refresh) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing tokens']);
    exit;
}

$user = supabaseGetUser($access);
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

setAuthCookies([
    'access_token'  => $access,
    'refresh_token' => $refresh,
    'expires_in'    => $expires,
]);

echo json_encode(['ok' => true]);
