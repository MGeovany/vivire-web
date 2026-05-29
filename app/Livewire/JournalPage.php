<?php

namespace App\Livewire;

use App\Models\Entry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JournalPage extends Component
{
    public string $feelings = '';
    public string $thoughts = '';
    public string $reflections = '';

    public function mount(): void
    {
        $this->loadToday();
    }

    public function updated(string $name): void
    {
        if (!in_array($name, ['feelings', 'thoughts', 'reflections'], true)) {
            return;
        }

        $this->saveSection($name, $this->$name);
    }

    private function loadToday(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        $today = CarbonImmutable::today();
        $entries = Entry::query()
            ->where('user_id', $userId)
            ->whereDate('entry_date', $today)
            ->whereIn('section', ['feelings', 'thoughts', 'reflections'])
            ->get()
            ->keyBy('section');

        $this->feelings = $this->blocksToText($entries->get('feelings')?->blocks);
        $this->thoughts = $this->blocksToText($entries->get('thoughts')?->blocks);
        $this->reflections = $this->blocksToText($entries->get('reflections')?->blocks);
    }

    private function saveSection(string $section, string $text): void
    {
        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        $today = CarbonImmutable::today()->toDateString();

        Entry::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'entry_date' => $today,
                'section' => $section,
            ],
            [
                'blocks' => $this->textToBlocks($text),
            ]
        );
    }

    private function textToBlocks(string $text): array
    {
        $text = rtrim($text);
        if ($text === '') {
            return [];
        }

        return [[
            'id' => 'main',
            'type' => 'text',
            'content' => $text,
            'metadata' => (object)[],
        ]];
    }

    private function blocksToText(null|array $blocks): string
    {
        if (!$blocks) {
            return '';
        }

        $first = $blocks[0] ?? null;
        if (!is_array($first)) {
            return '';
        }

        $content = $first['content'] ?? '';
        return is_string($content) ? $content : '';
    }

    public function render()
    {
        return view('livewire.journal-page');
    }
}
