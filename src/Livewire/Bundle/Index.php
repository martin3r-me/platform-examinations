<?php

namespace Platform\Examinations\Livewire\Bundle;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Examinations\Models\ExaminationBundle;

class Index extends Component
{
    public bool $showCreate = false;
    public array $form = ['name' => '', 'description' => ''];

    public function openCreate(): void
    {
        $this->reset('form');
        $this->resetErrorBag();
        $this->showCreate = true;
    }

    public function save()
    {
        $this->validate([
            'form.name'        => 'required|string|max:255',
            'form.description' => 'nullable|string',
        ]);

        $bundle = ExaminationBundle::create([
            'team_id'            => (int) Auth::user()->currentTeam->id,
            'created_by_user_id' => Auth::id(),
            'name'               => trim($this->form['name']),
            'description'        => $this->form['description'] !== '' ? trim($this->form['description']) : null,
            'status'             => 'active',
        ]);

        $this->showCreate = false;
        $this->reset('form');

        return $this->redirectRoute('examinations.bundles.show', ['bundle' => $bundle->id], navigate: true);
    }

    public function render()
    {
        $team = Auth::user()?->currentTeam?->id;

        $bundles = $team
            ? ExaminationBundle::forTeam($team)->withCount('examinations')->orderBy('position')->orderByDesc('id')->get()
            : collect();

        return view('examinations::livewire.bundle.index', ['bundles' => $bundles])
            ->layout('platform::layouts.app');
    }
}
