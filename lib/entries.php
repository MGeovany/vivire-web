<?php

function getEntriesByDate(string $userId, string $dateISO): array {
    $result = supabaseRequest('GET', '/entries', [
        'select'     => 'id,entry_date,section,blocks',
        'user_id'    => 'eq.' . $userId,
        'entry_date' => 'eq.' . $dateISO,
    ]);

    $body = $result['body'];
    if (!is_array($body) || !array_is_list($body)) {
        if (IS_DEV && is_array($body)) {
            error_log('[vivire] entries query error: ' . json_encode($body));
        }
        return [];
    }

    return $body;
}

function entryBlocks(array $entry): array {
    $blocks = $entry['blocks'] ?? [];
    if (is_string($blocks)) {
        $blocks = json_decode($blocks, true);
    }
    return is_array($blocks) ? $blocks : [];
}

/** Ensure profiles row exists (signup before trigger, or legacy users). */
function ensureProfile(string $userId, string $name = 'Usuario'): void {
    $check = supabaseRequest('GET', '/profiles', [
        'select' => 'id',
        'id'     => 'eq.' . $userId,
        'limit'  => '1',
    ]);
    $rows = $check['body'];
    if (is_array($rows) && array_is_list($rows) && count($rows) > 0) {
        return;
    }

    supabaseRequest('POST', '/profiles', [], [
        'id'   => $userId,
        'name' => $name,
    ], ['Prefer: return=minimal']);
}

/**
 * Upsert a journal entry. Returns ['ok' => bool, 'message' => ?string, 'code' => ?string].
 */
function upsertEntry(string $userId, string $dateISO, string $section, array $blocks): array {
    ensureProfile($userId);

    $result = supabaseRequest(
        'POST',
        '/entries?on_conflict=user_id,entry_date,section',
        [],
        [
            'user_id'    => $userId,
            'entry_date' => $dateISO,
            'section'    => $section,
            'blocks'     => $blocks,
        ],
        ['Prefer: resolution=merge-duplicates, return=minimal']
    );

    if ($result['status'] < 400) {
        return ['ok' => true, 'message' => null, 'code' => null];
    }

    $body = is_array($result['body']) ? $result['body'] : [];
    return [
        'ok'      => false,
        'message' => $body['message'] ?? 'Save failed',
        'code'    => $body['code'] ?? null,
    ];
}
