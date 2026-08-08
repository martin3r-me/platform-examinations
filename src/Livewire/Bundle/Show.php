<?php

namespace Platform\Examinations\Livewire\Bundle;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Models\ExaminationBundle;

class Show extends Component
{
    #[Locked]
    public int $bundleId;

    public array $form = ['name' => '', 'description' => '', 'status' => 'active'];

    /** Auswahl im Hinzufügen-Select (examination id als String). */
    public string $addExaminationId = '';

    public function mount(int $bundle): void
    {
        $model = $this->resolve($bundle);
        $this->bundleId = $model->id;
        $this->form = [
            'name'        => $model->name,
            'description' => $model->description ?? '',
            'status'      => $model->status,
        ];
    }

    protected function resolve(int $id): ExaminationBundle
    {
        return ExaminationBundle::forTeam((int) Auth::user()->currentTeam->id)->findOrFail($id);
    }

    public function save(): void
    {
        $this->validate([
            'form.name'   => 'required|string|max:255',
            'form.status' => 'required|in:active,archived',
        ]);

        $this->resolve($this->bundleId)->update([
            'name'        => trim($this->form['name']),
            'description' => $this->form['description'] !== '' ? trim($this->form['description']) : null,
            'status'      => $this->form['status'],
        ]);

        $this->dispatch('toast', message: 'Bündel gespeichert.', type: 'success');
    }

    public function addExamination(): void
    {
        if (!ctype_digit($this->addExaminationId)) {
            return;
        }
        $bundle = $this->resolve($this->bundleId);
        $exam   = Examination::forTeam((int) Auth::user()->currentTeam->id)->find((int) $this->addExaminationId);
        if (!$exam) {
            return;
        }

        // Vermengungsregel: nur EINE Gruppe je Bündel.
        if ($exam->combination_group) {
            $existing = $bundle->examinations()->pluck('combination_group')->filter()->unique();
            if ($existing->isNotEmpty() && !$existing->contains($exam->combination_group)) {
                $existingLabel = $existing->implode(', ');
                $this->dispatch('toast', type: 'error',
                    message: "Nicht möglich: {$exam->label()} gehört zur Vermengungsgruppe '{$exam->combination_group}', das Bündel enthält bereits '{$existingLabel}'. Ein Bündel darf nur eine Vermengungsgruppe enthalten.");
                return;
            }
        }

        $bundle->examinations()->syncWithoutDetaching([$exam->id => ['position' => $bundle->examinations()->count() + 1]]);
        $this->addExaminationId = '';
        $this->dispatch('toast', message: 'Untersuchung hinzugefügt.', type: 'success');
    }

    public function removeExamination(int $examinationId): void
    {
        $this->resolve($this->bundleId)->examinations()->detach($examinationId);
    }

    public function render()
    {
        $team   = (int) Auth::user()->currentTeam->id;
        $bundle = $this->resolve($this->bundleId)->load('examinations');

        $usedIds = $bundle->examinations->pluck('id')->all();
        $available = Examination::forTeam($team)->active()
            ->whereNotIn('id', $usedIds)->orderBy('number')->orderBy('title')->get()
            ->map(fn ($e) => ['value' => (string) $e->id, 'label' => $e->label()])->all();

        return view('examinations::livewire.bundle.show', [
            'bundle'          => $bundle,
            'availableOptions' => $available,
        ])->layout('platform::layouts.app');
    }
}
