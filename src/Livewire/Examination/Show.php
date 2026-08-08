<?php

namespace Platform\Examinations\Livewire\Examination;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Examinations\Models\Examination;

/**
 * Untersuchung – Detail (read + inline edit der Kernfelder).
 */
class Show extends Component
{
    #[Locked]
    public int $examinationId;

    public array $form = [];

    protected array $fields = ['number', 'title', 'category', 'legal_basis', 'description', 'regulation_label', 'status'];

    public function mount(int $examination): void
    {
        $model = $this->resolve($examination);
        $this->examinationId = $model->id;
        foreach ($this->fields as $f) {
            $this->form[$f] = $model->{$f};
        }
    }

    protected function resolve(int $id): Examination
    {
        return Examination::forTeam((int) Auth::user()->currentTeam->id)->findOrFail($id);
    }

    public function save(): void
    {
        $this->validate([
            'form.title'  => 'required|string|max:255',
            'form.number' => 'nullable|string|max:32',
            'form.status' => 'required|in:active,archived',
        ]);

        $model = $this->resolve($this->examinationId);
        $data = [];
        foreach ($this->fields as $f) {
            $data[$f] = $this->form[$f] === '' ? null : $this->form[$f];
        }
        $model->update($data);
        $this->dispatch('toast', message: 'Untersuchung gespeichert.', type: 'success');
    }

    public function delete()
    {
        $this->resolve($this->examinationId)->delete();
        return $this->redirectRoute('examinations.examinations.index', navigate: true);
    }

    public function render()
    {
        $examination = $this->resolve($this->examinationId);

        return view('examinations::livewire.examination.show', [
            'examination'     => $examination,
            'categoryOptions' => collect(config('examinations.categories', []))->map(fn ($l, $k) => ['value' => $k, 'label' => $l])->values()->all(),
        ])->layout('platform::layouts.app');
    }
}
