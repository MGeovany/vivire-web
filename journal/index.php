<?php

$user     = requireAuth();
$userId   = $user['sub'];
$today    = new DateTime('now');
$todayISO = $today->format('Y-m-d');

// Fetch today's entries
$todayEntries = getEntriesByDate($userId, $todayISO);
$todayBySection = [];
foreach ($todayEntries as $e) {
    $todayBySection[$e['section']] = $e['blocks'] ?? [];
}

// Compute year dates and fetch their entries
$yearDates   = getYearDates($today);
$yearEntries = [];
foreach ($yearDates as $i => $yearDate) {
    $sectionId = 'year' . ($i + 1);
    $rows = getEntriesByDate($userId, $yearDate->format('Y-m-d'));
    foreach ($rows as $e) {
        if ($e['section'] === $sectionId) {
            $yearEntries[$sectionId] = $e['blocks'] ?? [];
        }
    }
}

layout_head('vivire');
?>

<div id="app-screen" class="visible">

  <!-- ── Header ────────────────────────────────────────────────────────────── -->
  <div class="app-header">
    <span class="app-logo">vivire</span>
    <div class="app-header-right">
      <span class="app-date"><?= htmlspecialchars(formatDate($today)) ?></span>
      <span class="save-indicator" id="save-indicator"></span>
      <form method="POST" action="/logout" style="display:inline">
        <button type="submit" class="signout-btn">Salir</button>
      </form>
    </div>
  </div>
  <div class="divider"></div>

  <!-- ── Today sections ────────────────────────────────────────────────────── -->
  <div class="section-group">
    <p class="section-group-title">Reflexiones de hoy</p>

    <?php foreach (TODAY_SECTIONS as $sec):
      $blocks = $todayBySection[$sec['id']] ?? [];
    ?>
    <div class="journal-section" data-section="<?= $sec['id'] ?>" data-date="<?= $todayISO ?>">
      <div class="section-label"><?= htmlspecialchars($sec['label']) ?></div>

      <div class="block-editor" data-section="<?= $sec['id'] ?>" data-date="<?= $todayISO ?>">
        <?php if (empty($blocks)): ?>
          <div class="block-text" contenteditable="true"
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
  <div class="years-divider">Misma fecha, próximos años</div>

  <!-- ── Year sections ─────────────────────────────────────────────────────── -->
  <?php foreach ($yearDates as $i => $yearDate):
    $sectionId = 'year' . ($i + 1);
    $yearISO   = $yearDate->format('Y-m-d');
    $isToday   = $yearISO === $todayISO;
    $isFuture  = !$isToday && $yearDate > $today;
    $isPast    = !$isToday && !$isFuture;
    $wrapClass = 'year-section' . ($isFuture ? ' locked-year' : ($isPast ? ' past-year' : ''));
    $blocks    = $yearEntries[$sectionId] ?? [];
  ?>
  <div class="<?= $wrapClass ?>">
    <div class="year-section-date">
      <span class="year-badge"><?= $yearDate->format('Y') ?></span>
      <?= htmlspecialchars(formatDateShort($yearDate)) ?>
      <?php if ($isFuture): ?>
        <span class="section-lock-icon">🔒</span>
      <?php endif; ?>
    </div>

    <div class="journal-section" data-section="<?= $sectionId ?>" data-date="<?= $yearISO ?>">
      <div class="block-editor<?= $isFuture ? ' locked' : '' ?>"
           data-section="<?= $sectionId ?>" data-date="<?= $yearISO ?>">
        <?php if (empty($blocks)): ?>
          <div class="block-text"
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

<div class="toast" id="app-toast"></div>
<script src="/js/editor.js"></script>
<?php layout_foot();

// ── Helper: media add button + popup ─────────────────────────────────────────
function mediaAddButton(): string {
    return <<<HTML
<div class="add-media-wrap">
  <button class="add-media-btn" type="button" title="Añadir media">+</button>
  <div class="media-popup">
    <button class="media-popup-item" type="button" data-type="image"    data-accept="image/*"><span class="media-popup-icon">🖼</span>Imagen</button>
    <button class="media-popup-item" type="button" data-type="audio"    data-accept="audio/*"><span class="media-popup-icon">🎵</span>Audio</button>
    <button class="media-popup-item" type="button" data-type="video"    data-accept="video/*"><span class="media-popup-icon">🎬</span>Video</button>
    <button class="media-popup-item" type="button" data-type="document" data-accept="*/*">   <span class="media-popup-icon">📄</span>Documento</button>
  </div>
</div>
HTML;
}
?>
