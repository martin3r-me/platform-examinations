{{-- Bündel – Detail/Bearbeiten + Untersuchungen zuordnen --}}
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar :title="$bundle->name" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Untersuchungen', 'icon' => 'beaker', 'route' => 'examinations.dashboard'],
            ['label' => 'Bündel', 'route' => 'examinations.bundles.index'],
            ['label' => $bundle->name],
        ]">
            <x-nx-button variant="primary" size="sm" wire:click="save">
                @svg('heroicon-o-check', 'w-4 h-4') <span>Speichern</span>
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        <x-nx-section icon="heroicon-o-squares-2x2" title="Bündel">
            <x-nx-card>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-nx-input-text name="form.name" label="Name" wire:model="form.name" required />
                    </div>
                    <x-nx-input-select name="form.status" label="Status" wire:model="form.status"
                        :options="[['value'=>'active','label'=>'Aktiv'],['value'=>'archived','label'=>'Archiviert']]" />
                    <div class="md:col-span-3">
                        <x-nx-input-textarea name="form.description" label="Beschreibung" wire:model="form.description" :rows="2" />
                    </div>
                </div>
            </x-nx-card>
        </x-nx-section>

        <x-nx-section icon="heroicon-o-beaker" title="Untersuchungen im Bündel" :hint="$bundle->examinations->count()">
            <x-nx-card flush class="divide-y divide-[color:var(--nx-line)]">
                @forelse($bundle->examinations as $e)
                    <div class="flex items-center justify-between gap-3 p-4">
                        <div class="flex items-center gap-2 min-w-0">
                            @svg('heroicon-o-beaker', 'w-4 h-4 text-[color:var(--nx-muted)] shrink-0')
                            <span class="text-sm font-medium text-[color:var(--nx-text)] truncate">{{ $e->label() }}</span>
                            @if($e->combination_group)
                                <x-nx-badge variant="default">{{ config('examinations.combination_groups')[$e->combination_group] ?? $e->combination_group }}</x-nx-badge>
                            @endif
                        </div>
                        <button type="button" wire:click="removeExamination({{ $e->id }})"
                                class="text-xs text-[color:var(--nx-danger,#b42318)] hover:underline shrink-0">Entfernen</button>
                    </div>
                @empty
                    <div class="p-4 text-sm text-[color:var(--nx-muted)]">Noch keine Untersuchungen — unten hinzufügen.</div>
                @endforelse
            </x-nx-card>

            <x-nx-card>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <x-nx-input-select name="addExaminationId" label="Untersuchung hinzufügen" wire:model="addExaminationId"
                            nullable nullLabel="— wählen —" :options="$availableOptions" />
                    </div>
                    <x-nx-button variant="secondary" size="sm" wire:click="addExamination">
                        @svg('heroicon-o-plus', 'w-4 h-4') <span>Hinzufügen</span>
                    </x-nx-button>
                </div>
            </x-nx-card>
        </x-nx-section>
    </x-ui-page-container>
</x-ui-page>
