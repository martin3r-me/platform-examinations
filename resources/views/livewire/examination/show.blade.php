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
                    <x-nx-input-select name="form.combination_group" label="Vermengungsgruppe" wire:model="form.combination_group"
                        nullable nullLabel="— ohne (frei kombinierbar) —" :options="$combinationGroupOptions" />
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
    </x-ui-page-container>

    {{-- Linke Sidebar: Katalog-Navigation — schneller Wechsel zwischen Grundsätzen --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Katalog" icon="heroicon-o-beaker" width="w-72" :defaultOpen="true">
            <div class="p-3 space-y-1">
                <a href="{{ route('examinations.examinations.index') }}" wire:navigate
                   class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-[color:var(--nx-muted)] hover:bg-[color:var(--nx-hover)] transition-colors">
                    @svg('heroicon-o-arrow-left', 'w-4 h-4') Alle Untersuchungen
                </a>
                @foreach($catalog as $e)
                    <a href="{{ route('examinations.examinations.show', $e->id) }}" wire:navigate
                       @class([
                           'flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors',
                           'bg-[color:var(--nx-active)] text-[color:var(--nx-text)] font-medium' => $e->id === $examination->id,
                           'text-[color:var(--nx-muted)] hover:bg-[color:var(--nx-hover)]' => $e->id !== $examination->id,
                       ])>
                        <span class="min-w-0 truncate">{{ trim(($e->number ? $e->number.' · ' : '').$e->title) }}</span>
                    </a>
                @endforeach
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar: Gültigkeit & Stand (zeitlich/rechtliche Dimension) --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Gültigkeit & Stand" icon="heroicon-o-clock" width="w-72" :defaultOpen="true" storeKey="activityOpen" side="right">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-2">Version</h3>
                    <div class="text-sm text-[color:var(--nx-text)]">
                        v{{ $examination->version }}
                        @if(!$examination->isCurrentlyValid())
                            <span class="text-[color:var(--nx-warning,#b45309)]"> · nicht aktuell gültig</span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-2">Gültigkeit</h3>
                    <div class="text-sm text-[color:var(--nx-text)]">
                        {{ $examination->valid_from?->format('d.m.Y') ? 'ab '.$examination->valid_from->format('d.m.Y') : 'ab —' }}{{ $examination->valid_until?->format('d.m.Y') ? ' bis '.$examination->valid_until->format('d.m.Y') : ' (aktuell)' }}
                    </div>
                </div>

                @if($examination->regulation_label)
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[color:var(--nx-faint)] mb-2">Rechtsstand</h3>
                        <div class="text-sm text-[color:var(--nx-text)]">{{ $examination->regulation_label }}</div>
                    </div>
                @endif

                <div class="rounded-md bg-[color:var(--nx-subtle,rgba(0,0,0,0.03))] p-3 text-xs text-[color:var(--nx-muted)]">
                    Erbrachte Leistungen in der Sprechstunde referenzieren diesen Grundsatz (roter Faden Termin → Leistung → Katalog).
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
