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

function upsertEntry(string $userId, string $dateISO, string $section, array $blocks): bool {
    $result = supabaseRequest(
        'POST',
        '/entries',
        [],
        ['user_id' => $userId, 'entry_date' => $dateISO, 'section' => $section, 'blocks' => $blocks],
        ['Prefer: resolution=merge-duplicates', 'Prefer: return=minimal']
    );
    return $result['status'] < 400;
}
