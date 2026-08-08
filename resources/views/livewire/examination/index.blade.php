{{-- Untersuchungs-Katalog – Liste (nach Kategorie), Suche + Filter + Anlege-Modal --}}
@php
    $categoryOptions = collect($categories)->map(fn ($label, $key) => ['value' => $key, 'label' => $label])->values()->all();
@endphp

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Untersuchungen – Katalog" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Untersuchungen', 'icon' => 'beaker', 'route' => 'examinations.dashboard'],
            ['label' => 'Katalog'],
        ]">
            <x-nx-button variant="primary" size="sm" wire:click="openCreate">
                @svg('heroicon-o-plus', 'w-4 h-4')
                <span>Neue Untersuchung</span>
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        <x-nx-card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-nx-input-text name="search" label="Suche" wire:model.live.debounce.300ms="search"
                    placeholder="Titel, Nummer (G 20), Rechtsgrundlage …" />
                <x-nx-input-select name="filterCategory" label="Kategorie" wire:model.live="filterCategory"
                    nullable nullLabel="Alle Kategorien" :options="$categoryOptions" />
            </div>
        </x-nx-card>

        @if($total === 0)
            <x-nx-card>
                <x-nx-empty icon="heroicon-o-beaker">
                    Noch keine Untersuchungen im Katalog.
                    <x-slot name="action">
                        <x-nx-button variant="primary" size="sm" wire:click="openCreate">Untersuchung anlegen</x-nx-button>
                    </x-slot>
                </x-nx-empty>
            </x-nx-card>
        @else
            @foreach($grouped as $group)
                <x-nx-section icon="heroicon-o-folder" :title="$group['label']" :hint="$group['examinations']->count()">
                    <x-nx-card flush class="divide-y divide-[color:var(--nx-line)]">
                        @foreach($group['examinations'] as $ex)
                            <x-nx-list-item
                                :href="route('examinations.examinations.show', $ex)"
                                wire:navigate
                                icon="heroicon-o-beaker"
                                :title="$ex->label()"
                                :subtitle="$ex->legal_basis"
                                :meta="$ex->regulation_label" />
                        @endforeach
                    </x-nx-card>
                </x-nx-section>
            @endforeach
        @endif
    </x-ui-page-container>

    {{-- Innere Sidebar: Kategorie-Navigation (klickbar filtert die Liste) --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Kategorien" icon="heroicon-o-folder" width="w-72" :defaultOpen="true">
            <div class="p-3 space-y-1">
                <button type="button" wire:click="$set('filterCategory', '')"
                        @class([
                            'w-full flex items-center justify-between gap-2 rounded-md px-3 py-2 text-sm transition-colors',
                            'bg-[color:var(--nx-active)] text-[color:var(--nx-text)] font-medium' => $filterCategory === '',
                            'text-[color:var(--nx-muted)] hover:bg-[color:var(--nx-hover)]' => $filterCategory !== '',
                        ])>
                    <span>Alle Kategorien</span>
                    <span class="text-xs text-[color:var(--nx-faint)]">{{ array_sum($categoryCounts) }}</span>
                </button>
                @foreach($categories as $key => $label)
                    <button type="button" wire:click="$set('filterCategory', '{{ $key }}')"
                            @class([
                                'w-full flex items-center justify-between gap-2 rounded-md px-3 py-2 text-sm transition-colors text-left',
                                'bg-[color:var(--nx-active)] text-[color:var(--nx-text)] font-medium' => $filterCategory === $key,
                                'text-[color:var(--nx-muted)] hover:bg-[color:var(--nx-hover)]' => $filterCategory !== $key,
                            ])>
                        <span class="min-w-0 truncate">{{ $label }}</span>
                        @if(($categoryCounts[$key] ?? 0) > 0)
                            <span class="text-xs text-[color:var(--nx-faint)] shrink-0">{{ $categoryCounts[$key] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Anlege-Modal --}}
    <x-nx-modal size="lg" wire:model="showCreate">
        <x-slot name="header">Neue Untersuchung anlegen</x-slot>

        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-nx-input-text name="form.number" label="Nummer" wire:model="form.number" placeholder="z.B. G 20" />
                <div class="md:col-span-2">
                    <x-nx-input-text name="form.title" label="Titel" wire:model="form.title" placeholder="z.B. Lärm" required />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-nx-input-select name="form.category" label="Kategorie" wire:model="form.category"
                    nullable nullLabel="— ohne —" :options="$categoryOptions" />
                <x-nx-input-text name="form.legal_basis" label="Rechtsgrundlage" wire:model="form.legal_basis"
                    placeholder="z.B. DGUV Grundsatz G 20" />
            </div>
            <x-nx-input-textarea name="form.description" label="Beschreibung / Hinweise" wire:model="form.description" :rows="3" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-nx-input-date name="form.valid_from" label="Gültig ab" wire:model="form.valid_from" />
                <x-nx-input-date name="form.valid_until" label="Gültig bis" wire:model="form.valid_until" />
                <x-nx-input-text name="form.regulation_label" label="Rechtsstand" wire:model="form.regulation_label"
                    placeholder="z.B. DGUV Stand 2023" />
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-nx-button variant="ghost" wire:click="$set('showCreate', false)">Abbrechen</x-nx-button>
                <x-nx-button variant="primary" wire:click="save">Speichern</x-nx-button>
            </div>
        </x-slot>
    </x-nx-modal>
</x-ui-page>
