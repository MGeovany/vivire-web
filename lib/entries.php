<?php

function getEntriesByDate(string $userId, string $dateISO): array {
    $result = supabaseRequest('GET', '/entries', [
        'select'     => 'id,entry_date,section,blocks',
        'user_id'    => 'eq.' . $userId,
        'entry_date' => 'eq.' . $dateISO,
    ]);
    return is_array($result['body']) ? $result['body'] : [];
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
