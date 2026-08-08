{{-- Untersuchung – Detail/Bearbeiten --}}
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar :title="$examination->label()" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Untersuchungen', 'icon' => 'beaker', 'route' => 'examinations.dashboard'],
            ['label' => 'Katalog', 'route' => 'examinations.examinations.index'],
            ['label' => $examination->label()],
        ]">
            <x-nx-button variant="primary" size="sm" wire:click="save">
                @svg('heroicon-o-check', 'w-4 h-4') <span>Speichern</span>
            </x-nx-button>
            <x-nx-button variant="danger" size="sm" wire:click="delete" wire:confirm="Untersuchung wirklich löschen?">
                @svg('heroicon-o-trash', 'w-4 h-4')
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        <x-nx-section icon="heroicon-o-beaker" title="Untersuchung">
            <x-nx-card>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-nx-input-text name="form.number" label="Nummer" wire:model="form.number" placeholder="G 20" />
                    <div class="md:col-span-2">
                        <x-nx-input-text name="form.title" label="Titel" wire:model="form.title" required />
                    </div>
                    <x-nx-input-select name="form.category" label="Kategorie" wire:model="form.category"
                        nullable nullLabel="— ohne —" :options="$categoryOptions" />
                    <x-nx-input-text name="form.legal_basis" label="Rechtsgrundlage" wire:model="form.legal_basis" />
                    <x-nx-input-select name="form.status" label="Status" wire:model="form.status"
                        :options="[['value'=>'active','label'=>'Aktiv'],['value'=>'archived','label'=>'Archiviert']]" />
                    <div class="md:col-span-3">
                        <x-nx-input-textarea name="form.description" label="Beschreibung / Hinweise" wire:model="form.description" :rows="3" />
                    </div>
                    <x-nx-input-text name="form.regulation_label" label="Rechtsstand" wire:model="form.regulation_label" placeholder="DGUV Stand 2023" />
                </div>
            </x-nx-card>
        </x-nx-section>

        <x-nx-card flush class="divide-y divide-[color:var(--nx-line)]">
            <x-nx-property-row icon="heroicon-o-hashtag" label="Version">
                v{{ $examination->version }}{{ !$examination->isCurrentlyValid() ? ' · nicht aktuell gültig' : '' }}
            </x-nx-property-row>
            <x-nx-property-row icon="heroicon-o-clock" label="Gültigkeit">
                {{ $examination->valid_from?->format('d.m.Y') ? 'ab '.$examination->valid_from->format('d.m.Y') : 'ab —' }}{{ $examination->valid_until?->format('d.m.Y') ? ' bis '.$examination->valid_until->format('d.m.Y') : ' (aktuell)' }}
            </x-nx-property-row>
        </x-nx-card>
    </x-ui-page-container>

    {{-- Innere Sidebar: Eigenschaften im Überblick + Rücksprung --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Eigenschaften" icon="heroicon-o-beaker" width="w-72" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <a href="{{ route('examinations.examinations.index') }}" wire:navigate
                   class="flex items-center gap-2 text-sm text-[color:var(--nx-accent)] hover:underline">
                    @svg('heroicon-o-arrow-left', 'w-4 h-4') Zum Katalog
                </a>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-2">Kategorie</h3>
                    @if($examination->category)
                        <x-nx-badge variant="default">{{ config('examinations.categories')[$examination->category] ?? $examination->category }}</x-nx-badge>
                    @else
                        <div class="text-sm text-[color:var(--nx-muted)]">— ohne —</div>
                    @endif
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-2">Rechtsgrundlage</h3>
                    <div class="text-sm text-[color:var(--nx-text)]">{{ $examination->legal_basis ?: '—' }}</div>
                    @if($examination->regulation_label)
                        <div class="text-xs text-[color:var(--nx-muted)] mt-0.5">{{ $examination->regulation_label }}</div>
                    @endif
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-2">Status</h3>
                    <x-nx-badge :variant="$examination->status === 'active' ? 'success' : 'default'" dot>
                        {{ $examination->status === 'active' ? 'Aktiv' : 'Archiviert' }}
                    </x-nx-badge>
                    @if(!$examination->isCurrentlyValid())
                        <div class="text-xs text-[color:var(--nx-warning,#b45309)] mt-1">Aktuell nicht gültig</div>
                    @endif
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
