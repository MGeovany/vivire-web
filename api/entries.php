<?php
require_once __DIR__ . '/_helper.php';

corsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

// ── GET /api/entries?date=YYYY-MM-DD ─────────────────────────────────────────
if ($method === 'GET') {
    $userId = getAuthUser();

    $date = $_GET['date'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        jsonResponse(['error' => 'Missing or invalid date parameter (expected YYYY-MM-DD)'], 400);
    }

    $result = supabaseRequest('GET', '/entries', [
        'select'     => 'id,entry_date,section,blocks,created_at,updated_at',
        'user_id'    => 'eq.' . $userId,
        'entry_date' => 'eq.' . $date,
        'order'      => 'section.asc',
    ]);

    if ($result['status'] >= 400) {
        jsonResponse(['error' => 'Failed to fetch entries', 'detail' => $result['body']], $result['status']);
    }

    jsonResponse($result['body'] ?? []);
}

// ── POST /api/entries ─────────────────────────────────────────────────────────
if ($method === 'POST') {
    $userId = getAuthUser();

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        jsonResponse(['error' => 'Request body must be valid JSON'], 400);
    }

    // Validate required fields
    $date    = $data['entry_date'] ?? '';
    $section = $data['section']    ?? '';
    $blocks  = $data['blocks']     ?? [];

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        jsonResponse(['error' => 'Missing or invalid entry_date (expected YYYY-MM-DD)'], 400);
    }

    $allowedSections = ['feelings', 'thoughts', 'reflections', 'year1', 'year2', 'year3'];
    if (!in_array($section, $allowedSections, true)) {
        jsonResponse(['error' => 'Invalid section. Allowed: ' . implode(', ', $allowedSections)], 400);
    }

    if (!is_array($blocks)) {
        jsonResponse(['error' => 'blocks must be an array'], 400);
    }

    $payload = [
        'user_id'    => $userId,
        'entry_date' => $date,
        'section'    => $section,
        'blocks'     => $blocks,
    ];

    // Upsert: on conflict (user_id, entry_date, section) update blocks + updated_at
    $result = supabaseRequest(
        'POST',
        '/entries',
        [],
        $payload,
        [
            'Prefer: resolution=merge-duplicates',
            'Prefer: return=representation',
        ]
    );

    if ($result['status'] >= 400) {
        jsonResponse(['error' => 'Failed to save entry', 'detail' => $result['body']], $result['status']);
    }

    $saved = is_array($result['body']) && isset($result['body'][0])
        ? $result['body'][0]
        : $result['body'];

    jsonResponse($saved, 200);
}

// ── Method not allowed ────────────────────────────────────────────────────────
jsonResponse(['error' => 'Method not allowed'], 405);
