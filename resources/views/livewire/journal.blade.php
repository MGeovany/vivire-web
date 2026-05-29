<div>
  <div id="app-screen" class="block max-w-[680px] mx-auto px-6 pb-[100px] max-sm:px-[18px] max-sm:pb-[72px]">

    {{-- ── Header ───────────────────────────────────────────────────────────── --}}
    <div class="flex items-baseline justify-between pt-10 pb-[22px] max-sm:pt-7 max-sm:pb-[18px] max-sm:flex-wrap max-sm:gap-[10px]">
      <span class="font-serif text-lg font-normal text-muted tracking-[-0.2px]">vivire</span>
      <div class="flex items-center gap-[18px]">
        <span class="font-serif text-sm italic text-subtle max-sm:text-[13px]">{{ \App\Support\Dates::long($today) }}</span>
        <span class="save-indicator text-[11.5px] italic text-muted min-w-[68px] text-right select-none transition-colors duration-200" id="save-indicator"></span>
        <button type="button" id="sound-toggle" class="text-sm leading-none transition-opacity duration-150 hover:opacity-70" title="Sonido de tecleo">🔊</button>
        <button type="button" wire:click="logout" class="text-xs font-normal text-muted tracking-[0.02em] transition-colors duration-150 hover:text-subtle">Salir</button>
      </div>
    </div>
    <div class="h-px bg-border"></div>

    {{-- ── Today's three sections ───────────────────────────────────────────── --}}
    <div class="pt-11" wire:ignore>
      <p class="text-[10px] font-medium tracking-[0.12em] uppercase text-muted mb-8">Reflexiones de hoy</p>

      @foreach ($todaySections as $sec)
        @php $blocks = $todayEntries[$sec['id']]->blocks ?? []; @endphp
        <div class="mb-10" data-section="{{ $sec['id'] }}" data-date="{{ $todayISO }}">
          <div class="text-[10px] font-medium tracking-[0.12em] uppercase text-muted mb-3 flex items-center gap-2">{{ $sec['label'] }}</div>
          <div class="block-editor relative min-h-[72px]" data-section="{{ $sec['id'] }}" data-date="{{ $todayISO }}">
            {!! \App\Support\Blocks::renderAll($blocks, $sec['placeholder']) !!}
          </div>
          @include('journal._media-button')
        </div>
      @endforeach
    </div>

    {{-- ── Year divider ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4 my-14 text-muted text-[10px] tracking-[0.12em] uppercase font-medium">
      <span class="flex-1 h-px bg-border"></span>
      <span class="shrink-0">Misma fecha, próximos años</span>
      <span class="flex-1 h-px bg-border"></span>
    </div>

    {{-- ── Year sections (+1 / +2 / +3) ─────────────────────────────────────── --}}
    <div wire:ignore>
      @foreach ($years as $yr)
        <div class="mb-[52px]">
          <div class="text-[12.5px] font-normal text-subtle mb-[18px] flex items-center gap-2 {{ $yr['isFuture'] ? 'text-locked' : '' }}">
            <span class="text-[10px] font-medium tracking-[0.08em] uppercase text-muted bg-badge rounded px-[7px] py-[2px]">{{ $yr['date']->format('Y') }}</span>
            {{ \App\Support\Dates::short($yr['date']) }}
            @if ($yr['isFuture'])
              <span class="text-[10px] text-[#DDD]">🔒</span>
            @endif
          </div>

          <div class="mb-10 {{ $yr['isFuture'] ? 'opacity-35 pointer-events-none select-none' : ($yr['isPast'] ? 'pointer-events-none select-text' : '') }}"
               data-section="{{ $yr['sectionId'] }}" data-date="{{ $yr['iso'] }}">
            <div class="block-editor relative min-h-[72px] {{ $yr['isFuture'] ? 'locked' : '' }}"
                 data-section="{{ $yr['sectionId'] }}" data-date="{{ $yr['iso'] }}">
              {!! \App\Support\Blocks::renderAll($yr['blocks'], 'Escribe aquí…', $yr['isFuture']) !!}
            </div>
            @unless ($yr['isFuture'])
              @include('journal._media-button')
            @endunless
          </div>
        </div>
      @endforeach
    </div>

  </div>
</div>
