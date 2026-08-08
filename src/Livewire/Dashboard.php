<?php

namespace Platform\Examinations\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Examinations\Models\Examination;

class Dashboard extends Component
{
    public function render()
    {
        $team = Auth::user()?->currentTeam?->id;

        $total  = $team ? Examination::forTeam($team)->count() : 0;
        $active = $team ? Examination::forTeam($team)->active()->count() : 0;

        return view('examinations::livewire.dashboard', [
            'total'  => $total,
            'active' => $active,
        ])->layout('platform::layouts.app');
    }
}
