<div>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-serif italic tracking-tight">vivire</h1>
                <div class="text-sm text-[var(--color-subtle)]">{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</div>
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-xs tracking-wider uppercase text-[var(--color-subtle)]">Feelings</label>
            <textarea
                class="w-full rounded-2xl border border-[var(--color-border)] bg-white/60 backdrop-blur px-4 py-3 leading-relaxed focus:border-[var(--color-fg)] focus:ring-0"
                rows="5"
                wire:model.live.debounce.800ms="feelings"
            ></textarea>
        </div>

        <div class="space-y-2">
            <label class="block text-xs tracking-wider uppercase text-[var(--color-subtle)]">Thoughts</label>
            <textarea
                class="w-full rounded-2xl border border-[var(--color-border)] bg-white/60 backdrop-blur px-4 py-3 leading-relaxed focus:border-[var(--color-fg)] focus:ring-0"
                rows="5"
                wire:model.live.debounce.800ms="thoughts"
            ></textarea>
        </div>

        <div class="space-y-2">
            <label class="block text-xs tracking-wider uppercase text-[var(--color-subtle)]">Reflections</label>
            <textarea
                class="w-full rounded-2xl border border-[var(--color-border)] bg-white/60 backdrop-blur px-4 py-3 leading-relaxed focus:border-[var(--color-fg)] focus:ring-0"
                rows="5"
                wire:model.live.debounce.800ms="reflections"
            ></textarea>
        </div>
    </div>
</div>
