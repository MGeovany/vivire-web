<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = getAuthUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$date    = $data['entry_date'] ?? '';
$section = $data['section']    ?? '';
$blocks  = $data['blocks']     ?? [];

$allowed = ['feelings', 'thoughts', 'reflections', 'year1', 'year2', 'year3'];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !in_array($section, $allowed, true) || !is_array($blocks)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$result = upsertEntry($user['sub'], $date, $section, $blocks);

if ($result['ok']) {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(500);
$payload = ['error' => 'Save failed'];

if (IS_DEV) {
    $payload['detail'] = $result['message'];
    if ($result['code']) {
        $payload['code'] = $result['code'];
    }
    if ($result['code'] === 'PGRST205') {
        $payload['hint'] = 'Run supabase/schema.sql in the Supabase SQL Editor.';
    }
    error_log('[vivire] save failed: ' . json_encode($result));
}

echo json_encode($payload);
