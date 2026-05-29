<div>
  <div id="app-screen" class="block max-w-[640px] mx-auto px-5 pb-20 max-sm:px-4 max-sm:pb-14 animate-fade-in">

    <header class="sticky top-0 z-50 bg-bg/90 backdrop-blur-sm -mx-5 px-5 py-6 mb-4 max-sm:-mx-4 max-sm:px-4 max-sm:py-5 animate-fade-up">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <span class="font-write text-xl text-fg tracking-[-0.03em] leading-none block">vivire</span>
          <span class="text-[13px] text-subtle mt-1.5 block truncate">{{ \App\Support\Dates::long($today) }}</span>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 pt-0.5">
          <span class="save-indicator text-[11px] text-muted min-w-[68px] text-right select-none transition-colors duration-200" id="save-indicator"></span>

          <button type="button" id="sound-toggle"
                  class="w-7 h-7 flex items-center justify-center text-muted transition-colors duration-150 hover:text-fg"
                  title="Sonido de tecleo" aria-label="Alternar sonido de tecleo">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
              <path d="M11 5L6 9H3v6h3l5 4V5z"/><path d="M15.5 8.5a5 5 0 010 7M18 6a8 8 0 010 12"/>
            </svg>
          </button>

          <button type="button" wire:click="logout"
                  class="text-[13px] text-muted transition-colors hover:text-fg">
            Salir
          </button>
        </div>
      </div>
    </header>

    <section class="pt-2" wire:ignore>
      <p class="section-label mb-8">Hoy</p>

      @foreach ($todaySections as $sec)
        @php $blocks = $todayEntries[$sec['id']]->blocks ?? []; @endphp
        <article class="mb-11 animate-fade-up" style="animation-delay: {{ ($loop->index + 1) * 70 }}ms" data-section="{{ $sec['id'] }}" data-date="{{ $todayISO }}">
          <h2 class="section-label mb-3">{{ $sec['label'] }}</h2>
          <div class="block-editor relative min-h-[72px]" data-section="{{ $sec['id'] }}" data-date="{{ $todayISO }}">
            {!! \App\Support\Blocks::renderAll($blocks, $sec['placeholder']) !!}
          </div>
          @include('journal._media-button')
        </article>
      @endforeach
    </section>

    <div class="my-14 text-center text-[11px] tracking-[0.06em] uppercase text-muted animate-fade-in" style="animation-delay: 280ms">
      Misma fecha, próximos años
    </div>

    <section wire:ignore>
      @foreach ($years as $yr)
        <article @class([
          'mb-12 transition-opacity duration-300 animate-fade-up',
          'opacity-35 pointer-events-none select-none' => $yr['isFuture'],
          'opacity-80' => $yr['isPast'],
        ]) style="animation-delay: {{ ($loop->index + 4) * 70 }}ms">
          <div @class(['flex items-baseline gap-2 mb-4', 'text-locked' => $yr['isFuture']])>
            <span class="text-[13px] text-fg tabular-nums">{{ $yr['date']->format('Y') }}</span>
            <span class="text-[13px] text-muted">{{ \App\Support\Dates::short($yr['date']) }}</span>
            @if ($yr['isFuture'])
              <svg class="w-3 h-3 text-muted/60 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
            @endif
          </div>

          <div data-section="{{ $yr['sectionId'] }}" data-date="{{ $yr['iso'] }}">
            <div @class(['block-editor relative min-h-[56px]', 'locked' => $yr['isFuture']]) data-section="{{ $yr['sectionId'] }}" data-date="{{ $yr['iso'] }}">
              {!! \App\Support\Blocks::renderAll($yr['blocks'], 'Escribe aquí…', $yr['isFuture']) !!}
            </div>
            @unless ($yr['isFuture'])
              @include('journal._media-button')
            @endunless
          </div>
        </article>
      @endforeach
    </section>

  </div>
</div>
