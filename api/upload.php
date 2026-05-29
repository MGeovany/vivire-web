<?php

header('Content-Type: application/json; charset=utf-8');

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

$file = $_FILES['file'] ?? null;
$type = $_POST['type'] ?? 'document';

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$maxBytes = 50 * 1024 * 1024;
if ($file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large (max 50 MB)']);
    exit;
}

$userId   = $user['sub'];
$origName = basename($file['name']);
$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$uid      = bin2hex(random_bytes(8));
$path     = "{$userId}/{$type}/{$uid}.{$ext}";
$mimeType = $file['type'] ?: 'application/octet-stream';

$fileData  = file_get_contents($file['tmp_name']);
$publicUrl = supabaseStorageUpload($path, $fileData, $mimeType);

if (!$publicUrl) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

echo json_encode([
    'url'  => $publicUrl,
    'name' => $origName,
    'size' => $file['size'],
]);
