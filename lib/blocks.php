<?php

/** Render a single content block as HTML. */
function renderBlock(array $block, string $placeholder, bool $locked = false): void {
    $type    = $block['type']    ?? 'text';
    $content = $block['content'] ?? '';
    $id      = $block['id']      ?? '';
    $meta    = $block['metadata'] ?? [];

    switch ($type) {
        case 'text':
            $ce = $locked ? 'false' : 'true';
            $ph = htmlspecialchars($placeholder);
            echo "<div class=\"block-text\" contenteditable=\"{$ce}\" data-placeholder=\"{$ph}\">"
               . htmlspecialchars($content)
               . "</div>\n";
            break;

        case 'image':
            $src = htmlspecialchars($content);
            $alt = htmlspecialchars($meta['name'] ?? 'Imagen');
            echo "<div class=\"block-media\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"image\">"
               . "<img src=\"{$src}\" alt=\"{$alt}\" loading=\"lazy\">"
               . "</div>\n";
            break;

        case 'audio':
            $src = htmlspecialchars($content);
            echo "<div class=\"block-media\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"audio\">"
               . "<audio src=\"{$src}\" controls></audio>"
               . "</div>\n";
            break;

        case 'video':
            $src = htmlspecialchars($content);
            echo "<div class=\"block-media\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"video\">"
               . "<video src=\"{$src}\" controls playsinline></video>"
               . "</div>\n";
            break;

        case 'document':
            $name = $meta['name'] ?? 'Documento';
            $size = (int) ($meta['size'] ?? 0);
            $href = htmlspecialchars($content);
            $icon = docIcon($name);
            $disp = $size ? formatFileSize($size) : 'Documento adjunto';
            echo "<div class=\"block-media\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"document\">"
               . "<a href=\"{$href}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"block-doc-card\">"
               . "<span class=\"block-doc-icon\">{$icon}</span>"
               . "<div class=\"block-doc-info\">"
               . "<div class=\"block-doc-name\">" . htmlspecialchars($name) . "</div>"
               . "<div class=\"block-doc-meta\" data-size=\"{$size}\">" . htmlspecialchars($disp) . "</div>"
               . "</div></a></div>\n";
            break;
    }
}

function docIcon(string $name): string {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext === 'pdf') return '📕';
    if (in_array($ext, ['doc', 'docx'])) return '📝';
    if (in_array($ext, ['xls', 'xlsx', 'csv'])) return '📊';
    if (in_array($ext, ['ppt', 'pptx'])) return '📋';
    if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) return '🗜';
    return '📄';
}

function formatFileSize(int $bytes): string {
    if ($bytes < 1024)    return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
