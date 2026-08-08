<?php

namespace Platform\Examinations\Livewire;

use Livewire\Component;
use Platform\Examinations\Models\Examination;

/**
 * Modul-Nav-Sidebar für das Untersuchungs-Katalog-Modul.
 */
class Sidebar extends Component
{
    public function render()
    {
        $team = auth()->user()?->currentTeam;

        $categoryCounts = [];
        if ($team) {
            $categoryCounts = Examination::forTeam($team->id)->active()
                ->selectRaw('category, COUNT(*) as c')->groupBy('category')
                ->pluck('c', 'category')->all();
        }

        return view('examinations::livewire.sidebar', [
            'categories'     => config('examinations.categories', []),
            'categoryCounts' => $categoryCounts,
        ]);
    }
}
