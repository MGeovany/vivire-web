<div class="relative inline-block mt-2">
  <button type="button" title="Añadir media" aria-label="Añadir media"
          class="add-media-btn text-[13px] text-muted transition-colors duration-150 hover:text-fg">
    + adjuntar
  </button>

  <div class="media-popup absolute bottom-[calc(100%+8px)] left-0 bg-surface border border-border rounded-xl p-1 min-w-[148px] z-[100] shadow-[0_4px_24px_rgb(0_0_0/0.06)]">
    <button type="button" data-type="image" data-accept="image/*"
            class="media-popup-item flex items-center gap-2 py-2 px-2.5 rounded-lg text-[13px] text-fg w-full text-left transition-colors hover:bg-hover">
      Imagen
    </button>
    <button type="button" data-type="audio" data-accept="audio/*"
            class="media-popup-item flex items-center gap-2 py-2 px-2.5 rounded-lg text-[13px] text-fg w-full text-left transition-colors hover:bg-hover">
      Audio
    </button>
    <button type="button" data-type="video" data-accept="video/*"
            class="media-popup-item flex items-center gap-2 py-2 px-2.5 rounded-lg text-[13px] text-fg w-full text-left transition-colors hover:bg-hover">
      Video
    </button>
    <button type="button" data-type="document" data-accept="*/*"
            class="media-popup-item flex items-center gap-2 py-2 px-2.5 rounded-lg text-[13px] text-fg w-full text-left transition-colors hover:bg-hover">
      Documento
    </button>
  </div>
</div>
