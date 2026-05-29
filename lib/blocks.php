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
            echo "<div class=\"block-text w-full font-lora text-[17px] font-normal leading-[1.78] text-fg outline-none border-none bg-transparent py-[2px] caret-fg break-words whitespace-pre-wrap\" contenteditable=\"{$ce}\" data-placeholder=\"{$ph}\">"
               . htmlspecialchars($content)
               . "</div>\n";
            break;

        case 'image':
            $src = htmlspecialchars($content);
            $alt = htmlspecialchars($meta['name'] ?? 'Imagen');
            echo "<div class=\"block-media my-3 relative\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"image\">"
               . "<img src=\"{$src}\" alt=\"{$alt}\" loading=\"lazy\" class=\"max-w-full w-full h-auto rounded-lg border border-border block\">"
               . "</div>\n";
            break;

        case 'audio':
            $src = htmlspecialchars($content);
            echo "<div class=\"block-media my-3 relative\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"audio\">"
               . "<audio src=\"{$src}\" controls class=\"w-full my-1 accent-fg h-9\"></audio>"
               . "</div>\n";
            break;

        case 'video':
            $src = htmlspecialchars($content);
            echo "<div class=\"block-media my-3 relative\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"video\">"
               . "<video src=\"{$src}\" controls playsinline class=\"max-w-full w-full rounded-lg border border-border block\"></video>"
               . "</div>\n";
            break;

        case 'document':
            $name = $meta['name'] ?? 'Documento';
            $size = (int) ($meta['size'] ?? 0);
            $href = htmlspecialchars($content);
            $icon = docIcon($name);
            $disp = $size ? formatFileSize($size) : 'Documento adjunto';
            echo "<div class=\"block-media my-3 relative\" data-block-id=\"" . htmlspecialchars($id) . "\" data-block-type=\"document\">"
               . "<a href=\"{$href}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"block-doc-card flex items-center gap-3 px-4 py-3 border border-border rounded-lg transition-[border-color,background] duration-150 hover:border-muted hover:bg-white\">"
               . "<span class=\"block-doc-icon text-lg shrink-0\">{$icon}</span>"
               . "<div class=\"block-doc-info min-w-0\">"
               . "<div class=\"block-doc-name text-[13.5px] text-fg truncate\">" . htmlspecialchars($name) . "</div>"
               . "<div class=\"block-doc-meta text-[11.5px] text-subtle mt-px\" data-size=\"{$size}\">" . htmlspecialchars($disp) . "</div>"
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
