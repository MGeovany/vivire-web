<?php

$user     = requireAuth();
$userId   = $user['sub'];
$today    = new DateTime('now');
$todayISO = $today->format('Y-m-d');

// Fetch today's entries
$todayEntries = getEntriesByDate($userId, $todayISO);
$todayBySection = [];
foreach ($todayEntries as $e) {
    if (!is_array($e) || !isset($e['section'])) continue;
    $todayBySection[$e['section']] = entryBlocks($e);
}

// Compute year dates and fetch their entries
$yearDates   = getYearDates($today);
$yearEntries = [];
foreach ($yearDates as $i => $yearDate) {
    $sectionId = 'year' . ($i + 1);
    $rows = getEntriesByDate($userId, $yearDate->format('Y-m-d'));
    foreach ($rows as $e) {
        if (!is_array($e) || !isset($e['section'])) continue;
        if ($e['section'] === $sectionId) {
            $yearEntries[$sectionId] = entryBlocks($e);
        }
    }
}

layout_head('vivire');
?>

<div id="app-screen" class="block max-w-[680px] mx-auto px-6 pb-[100px] max-sm:px-[18px] max-sm:pb-[72px]">
  <!-- ── Header ────────────────────────────────────────────────────────────── -->
  <div class="flex items-baseline justify-between pt-10 pb-[22px] max-sm:pt-7 max-sm:pb-[18px] max-sm:flex-wrap max-sm:gap-[10px]">
    <span class="font-serif text-lg font-normal text-muted tracking-[-0.2px]">vivire</span>
    <div class="flex items-center gap-[18px]">
      <span class="font-serif text-sm italic text-subtle max-sm:text-[13px]"><?= htmlspecialchars(formatDate($today)) ?></span>
      <span class="save-indicator text-[11.5px] italic text-muted min-w-[68px] text-right select-none transition-colors duration-200" id="save-indicator"></span>
      <form method="POST" action="/logout" style="display:inline">
        <button type="submit" class="text-xs font-normal text-muted tracking-[0.02em] transition-colors duration-150 hover:text-subtle">Salir</button>
      </form>
    </div>
  </div>
  <div class="h-px bg-border"></div>

  <!-- ── Today sections ────────────────────────────────────────────────────── -->
  <div class="pt-11">
    <p class="text-[10px] font-medium tracking-[0.12em] uppercase text-muted mb-8">Reflexiones de hoy</p>

    <?php foreach (TODAY_SECTIONS as $sec):
      $blocks = $todayBySection[$sec['id']] ?? [];
    ?>
    <div class="mb-10" data-section="<?= $sec['id'] ?>" data-date="<?= $todayISO ?>">
      <div class="text-[10px] font-medium tracking-[0.12em] uppercase text-muted mb-3 flex items-center gap-2"><?= htmlspecialchars($sec['label']) ?></div>

      <div class="block-editor relative min-h-[72px]" data-section="<?= $sec['id'] ?>" data-date="<?= $todayISO ?>">
        <?php if (empty($blocks)): ?>
          <div class="block-text w-full font-lora text-[17px] font-normal leading-[1.78] text-fg outline-none border-none bg-transparent py-[2px] caret-fg break-words whitespace-pre-wrap max-sm:text-[16px]" contenteditable="true"
               data-placeholder="<?= htmlspecialchars($sec['placeholder']) ?>"></div>
        <?php else:
          foreach ($blocks as $block) renderBlock($block, $sec['placeholder']);
        endif; ?>
      </div>

      <?= mediaAddButton() ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Year divider ──────────────────────────────────────────────────────── -->
  <div class="flex items-center gap-4 my-14 text-muted text-[10px] tracking-[0.12em] uppercase font-medium">
    <span class="flex-1 h-px bg-border"></span>
    <span class="shrink-0">Misma fecha, próximos años</span>
    <span class="flex-1 h-px bg-border"></span>
  </div>

  <!-- ── Year sections ─────────────────────────────────────────────────────── -->
  <?php foreach ($yearDates as $i => $yearDate):
    $sectionId = 'year' . ($i + 1);
    $yearISO   = $yearDate->format('Y-m-d');
    $isToday   = $yearISO === $todayISO;
    $isFuture  = !$isToday && $yearDate > $today;
    $isPast    = !$isToday && !$isFuture;
    $yearLocked = '';
    if ($isFuture) $yearLocked = 'year-locked';
    elseif ($isPast) $yearLocked = 'year-past';
    $blocks    = $yearEntries[$sectionId] ?? [];
  ?>
  <div class="mb-[52px] <?= $yearLocked ?>">
    <div class="text-[12.5px] font-normal text-subtle mb-[18px] flex items-center gap-2 <?= $isFuture ? 'text-locked' : '' ?>">
      <span class="text-[10px] font-medium tracking-[0.08em] uppercase text-muted bg-badge rounded px-[7px] py-[2px]"><?= $yearDate->format('Y') ?></span>
      <?= htmlspecialchars(formatDateShort($yearDate)) ?>
      <?php if ($isFuture): ?>
        <span class="text-[10px] text-[#DDD]">🔒</span>
      <?php endif; ?>
    </div>

    <div class="mb-10 <?= $isFuture ? 'opacity-35 pointer-events-none select-none' : ($isPast ? 'pointer-events-none select-text' : '') ?>" data-section="<?= $sectionId ?>" data-date="<?= $yearISO ?>">
      <div class="block-editor relative min-h-[72px] <?= $isFuture ? 'opacity-40 pointer-events-none select-none' : '' ?>"
           data-section="<?= $sectionId ?>" data-date="<?= $yearISO ?>">
        <?php if (empty($blocks)): ?>
          <div class="block-text w-full font-lora text-[17px] font-normal leading-[1.78] text-fg outline-none border-none bg-transparent py-[2px] caret-fg break-words whitespace-pre-wrap max-sm:text-[16px]"
               contenteditable="<?= $isFuture ? 'false' : 'true' ?>"
               data-placeholder="Escribe aquí…"></div>
        <?php else:
          foreach ($blocks as $block) renderBlock($block, 'Escribe aquí…', $isFuture);
        endif; ?>
      </div>

      <?php if (!$isFuture): echo mediaAddButton(); endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

</div><!-- #app-screen -->

<script src="/js/editor.js"></script>
<?php layout_foot();

// ── Helper: media add button + popup ─────────────────────────────────────────
function mediaAddButton(): string {
    return <<<HTML
<div class="relative inline-block mt-[10px] max-sm:[&_.media-popup]:bottom-auto max-sm:[&_.media-popup]:top-[calc(100%+8px)]">
  <button class="w-[22px] h-[22px] rounded-full border border-border text-muted text-[15px] leading-none flex items-center justify-center transition-[border-color,color] duration-150 cursor-pointer hover:border-subtle hover:text-subtle" type="button" title="Añadir media">+</button>
  <div class="media-popup hidden absolute bottom-[calc(100%+8px)] left-0 bg-white border border-border rounded-xl p-[5px] min-w-[148px] z-[100] shadow-[0_8px_24px_rgba(0,0,0,0.07)] open:block">
    <button class="flex items-center gap-[9px] py-2 px-[10px] rounded-md text-sm text-fg cursor-pointer transition-[background] duration-100 w-full text-left hover:bg-hover" type="button" data-type="image"    data-accept="image/*"><span class="text-sm w-[18px] text-center shrink-0">🖼</span>Imagen</button>
    <button class="flex items-center gap-[9px] py-2 px-[10px] rounded-md text-sm text-fg cursor-pointer transition-[background] duration-100 w-full text-left hover:bg-hover" type="button" data-type="audio"    data-accept="audio/*"><span class="text-sm w-[18px] text-center shrink-0">🎵</span>Audio</button>
    <button class="flex items-center gap-[9px] py-2 px-[10px] rounded-md text-sm text-fg cursor-pointer transition-[background] duration-100 w-full text-left hover:bg-hover" type="button" data-type="video"    data-accept="video/*"><span class="text-sm w-[18px] text-center shrink-0">🎬</span>Video</button>
    <button class="flex items-center gap-[9px] py-2 px-[10px] rounded-md text-sm text-fg cursor-pointer transition-[background] duration-100 w-full text-left hover:bg-hover" type="button" data-type="document" data-accept="*/*">   <span class="text-sm w-[18px] text-center shrink-0">📄</span>Documento</button>
  </div>
</div>
HTML;
}
?>
