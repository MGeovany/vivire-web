<div>
    <div class="mx-auto max-w-3xl px-4 py-8 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Journal</h1>
            <div class="text-sm text-gray-500">{{ now()->toDateString() }}</div>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium">Feelings</label>
            <textarea
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                rows="5"
                wire:model.live.debounce.800ms="feelings"
            ></textarea>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium">Thoughts</label>
            <textarea
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                rows="5"
                wire:model.live.debounce.800ms="thoughts"
            ></textarea>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium">Reflections</label>
            <textarea
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                rows="5"
                wire:model.live.debounce.800ms="reflections"
            ></textarea>
        </div>
    </div>
</div>
