<?php

namespace App\Support;

class Blocks
{
    /** Tailwind classes shared by every editable text block. */
    public const TEXT_CLASSES = 'block-text w-full font-lora text-[17px] font-normal leading-[1.78] '
        . 'text-fg outline-none border-none bg-transparent py-[2px] caret-fg break-words '
        . 'whitespace-pre-wrap max-sm:text-[16px]';

    /**
     * Render every block of a section (or a single empty text block if none).
     *
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public static function renderAll(array $blocks, string $placeholder, bool $locked = false): string
    {
        if (empty($blocks)) {
            $ce = $locked ? 'false' : 'true';
            $ph = e($placeholder);

            return "<div class=\"" . self::TEXT_CLASSES . "\" contenteditable=\"{$ce}\" data-placeholder=\"{$ph}\"></div>\n";
        }

        $html = '';
        foreach ($blocks as $block) {
            $html .= self::render((array) $block, $placeholder, $locked);
        }

        return $html;
    }

    /** Render a single content block as HTML. */
    public static function render(array $block, string $placeholder, bool $locked = false): string
    {
        $type    = $block['type']     ?? 'text';
        $content = $block['content']  ?? '';
        $id      = (string) ($block['id'] ?? '');
        $meta    = $block['metadata'] ?? [];

        return match ($type) {
            'text'     => self::text($content, $placeholder, $locked),
            'image'    => self::image($content, $id, $meta, $locked),
            'audio'    => self::audio($content, $id),
            'video'    => self::video($content, $id),
            'document' => self::document($content, $id, $meta),
            default    => '',
        };
    }

    private static function text(string $content, string $placeholder, bool $locked): string
    {
        $ce = $locked ? 'false' : 'true';

        return '<div class="' . self::TEXT_CLASSES . "\" contenteditable=\"{$ce}\" data-placeholder=\"" . e($placeholder) . '">'
            . e($content) . "</div>\n";
    }

    private static function image(string $content, string $id, array $meta, bool $locked): string
    {
        // Float side + size persist in metadata; fall back to a stable hash so old
        // entries keep a consistent (but varied) layout across reloads.
        $float = $meta['float'] ?? ((crc32($id) % 2) ? 'right' : 'left');
        $size  = $meta['size']  ?? (['s', 'm', 'l'][crc32($id . 'z') % 3]);
        $float = in_array($float, ['left', 'right'], true) ? $float : 'left';
        $size  = in_array($size, ['s', 'm', 'l'], true) ? $size : 'm';
        $drag  = $locked ? '' : ' draggable="true"';
        $alt   = e($meta['name'] ?? 'Imagen');

        return '<div class="block-media" data-block-id="' . e($id) . '" data-block-type="image" '
            . "data-float=\"{$float}\" data-size=\"{$size}\"{$drag}>"
            . '<img src="' . e($content) . "\" alt=\"{$alt}\" loading=\"lazy\" "
            . 'class="w-full h-auto rounded-lg border border-border block"></div>' . "\n";
    }

    private static function audio(string $content, string $id): string
    {
        return '<div class="block-media my-3 relative" data-block-id="' . e($id) . '" data-block-type="audio">'
            . '<audio src="' . e($content) . '" controls class="w-full my-1 accent-fg h-9"></audio></div>' . "\n";
    }

    private static function video(string $content, string $id): string
    {
        return '<div class="block-media my-3 relative" data-block-id="' . e($id) . '" data-block-type="video">'
            . '<video src="' . e($content) . '" controls playsinline class="max-w-full w-full rounded-lg border border-border block"></video></div>' . "\n";
    }

    private static function document(string $content, string $id, array $meta): string
    {
        $name = $meta['name'] ?? 'Documento';
        $size = (int) ($meta['size'] ?? 0);
        $disp = $size ? self::fileSize($size) : 'Documento adjunto';

        return '<div class="block-media my-3 relative" data-block-id="' . e($id) . '" data-block-type="document">'
            . '<a href="' . e($content) . '" target="_blank" rel="noopener noreferrer" '
            . 'class="block-doc-card flex items-center gap-3 px-4 py-3 border border-border rounded-lg transition-[border-color,background] duration-150 hover:border-muted hover:bg-white">'
            . '<span class="block-doc-icon text-lg shrink-0">' . self::docIcon($name) . '</span>'
            . '<div class="block-doc-info min-w-0">'
            . '<div class="block-doc-name text-[13.5px] text-fg truncate">' . e($name) . '</div>'
            . "<div class=\"block-doc-meta text-[11.5px] text-subtle mt-px\" data-size=\"{$size}\">" . e($disp) . '</div>'
            . '</div></a></div>' . "\n";
    }

    public static function docIcon(string $name): string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return match (true) {
            $ext === 'pdf'                                  => '📕',
            in_array($ext, ['doc', 'docx'])                 => '📝',
            in_array($ext, ['xls', 'xlsx', 'csv'])          => '📊',
            in_array($ext, ['ppt', 'pptx'])                 => '📋',
            in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz']) => '🗜',
            default                                         => '📄',
        };
    }

    public static function fileSize(int $bytes): string
    {
        if ($bytes < 1024)    return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';

        return round($bytes / 1048576, 1) . ' MB';
    }
}
