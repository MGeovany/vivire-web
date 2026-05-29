<?php

namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Support\Dates;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vivire')]
class Journal extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/login', navigate: true);
    }

    /** Today's three reflection sections. */
    public const TODAY_SECTIONS = [
        ['id' => 'feelings',    'label' => 'Cómo me sentí', 'placeholder' => 'Describe cómo te sentiste hoy…'],
        ['id' => 'thoughts',    'label' => 'Qué pensé',     'placeholder' => 'Qué pensamientos tuviste hoy…'],
        ['id' => 'reflections', 'label' => 'Reflexiones',   'placeholder' => 'Reflexiona sobre el día…'],
    ];

    public function render()
    {
        $user     = Auth::user();
        $today     = Carbon::today();
        $todayISO  = $today->toDateString();

        $todayEntries = $user->entries()
            ->where('entry_date', $todayISO)
            ->get()
            ->keyBy('section');

        $years = [];
        foreach (Dates::nextYears($today) as $i => $date) {
            $sectionId = 'year' . ($i + 1);
            $iso       = $date->toDateString();
            $isToday   = $iso === $todayISO;
            $isFuture  = ! $isToday && $date->gt($today);

            $entry = $user->entries()
                ->where('entry_date', $iso)
                ->where('section', $sectionId)
                ->first();

            $years[] = [
                'sectionId' => $sectionId,
                'date'      => $date,
                'iso'       => $iso,
                'isFuture'  => $isFuture,
                'isPast'    => ! $isToday && ! $isFuture,
                'blocks'    => $entry?->blocks ?? [],
            ];
        }

        return view('livewire.journal', [
            'user'          => $user,
            'today'         => $today,
            'todayISO'      => $todayISO,
            'todaySections' => self::TODAY_SECTIONS,
            'todayEntries'  => $todayEntries,
            'years'         => $years,
        ]);
    }
}
