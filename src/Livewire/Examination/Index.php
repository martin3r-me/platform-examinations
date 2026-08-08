<?php

namespace Platform\Examinations\Livewire\Examination;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Services\ExaminationService;

/**
 * Untersuchungs-Katalog: Liste (nach Kategorie gruppiert) mit Suche + Anlege-Modal.
 */
class Index extends Component
{
    public string $search = '';
    public string $filterCategory = '';

    public bool $showCreate = false;
    public array $form = [
        'number'           => '',
        'title'            => '',
        'category'         => '',
        'legal_basis'      => '',
        'description'      => '',
        'valid_from'       => '',
        'valid_until'      => '',
        'regulation_label' => '',
    ];

    protected function rules(): array
    {
        return [
            'form.number'           => 'nullable|string|max:32',
            'form.title'            => 'required|string|max:255',
            'form.category'         => 'nullable|string|max:255',
            'form.legal_basis'      => 'nullable|string|max:255',
            'form.description'      => 'nullable|string',
            'form.valid_from'       => 'nullable|date',
            'form.valid_until'      => 'nullable|date',
            'form.regulation_label' => 'nullable|string|max:255',
        ];
    }

    public function openCreate(): void
    {
        $this->reset('form');
        $this->form['category'] = $this->filterCategory ?: '';
        $this->resetErrorBag();
        $this->showCreate = true;
    }

    public function save(ExaminationService $service): void
    {
        $this->validate();

        $team = Auth::user()->currentTeam;

        $service->create([
            'team_id'            => $team->id,
            'created_by_user_id' => Auth::id(),
            'number'             => $this->form['number'] !== '' ? trim($this->form['number']) : null,
            'title'              => trim($this->form['title']),
            'category'           => $this->form['category'] !== '' ? $this->form['category'] : null,
            'legal_basis'        => $this->form['legal_basis'] !== '' ? trim($this->form['legal_basis']) : null,
            'description'        => $this->form['description'] !== '' ? trim($this->form['description']) : null,
            'valid_from'         => $this->form['valid_from'] !== '' ? $this->form['valid_from'] : null,
            'valid_until'        => $this->form['valid_until'] !== '' ? $this->form['valid_until'] : null,
            'regulation_label'   => $this->form['regulation_label'] !== '' ? trim($this->form['regulation_label']) : null,
        ]);

        $this->showCreate = false;
        $this->reset('form');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $team = Auth::user()?->currentTeam;

        $query = $team ? Examination::forTeam($team->id) : Examination::whereRaw('1 = 0');

        if ($this->filterCategory !== '') {
            $query->where('category', $this->filterCategory);
        }
        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('number', 'like', "%{$s}%")
                  ->orWhere('legal_basis', 'like', "%{$s}%");
            });
        }

        $examinations = $query->orderBy('category')->orderBy('position')->orderBy('number')->get();

        $categories = config('examinations.categories', []);

        // Gruppiert: bekannte Kategorien zuerst, dann Rest (inkl. null).
        $grouped = collect($categories)
            ->map(fn ($label, $key) => [
                'key'          => $key,
                'label'        => $label,
                'examinations' => $examinations->where('category', $key)->values(),
            ])
            ->filter(fn ($g) => $g['examinations']->isNotEmpty())
            ->values();

        $ungrouped = $examinations->filter(fn ($e) => !array_key_exists((string) $e->category, $categories))->values();
        if ($ungrouped->isNotEmpty()) {
            $grouped->push(['key' => '_other', 'label' => 'Ohne Kategorie', 'examinations' => $ungrouped]);
        }

        return view('examinations::livewire.examination.index', [
            'grouped'    => $grouped,
            'total'      => $examinations->count(),
            'categories' => $categories,
        ])->layout('platform::layouts.app');
    }
}
