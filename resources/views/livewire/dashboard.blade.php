{{-- Untersuchungen · Dashboard --}}
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Untersuchungen" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[['label' => 'Untersuchungen', 'icon' => 'beaker']]">
            <x-nx-button variant="primary" size="sm" :href="route('examinations.examinations.index')" wire:navigate>
                @svg('heroicon-o-beaker', 'w-4 h-4') <span>Katalog öffnen</span>
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-nx-stat label="Untersuchungen im Katalog" :value="$total" icon="heroicon-o-beaker" accent="var(--nx-accent)" />
            <x-nx-stat label="Aktiv" :value="$active" icon="heroicon-o-check-circle" />
        </div>
        <x-nx-card>
            <p class="text-sm text-[color:var(--nx-muted)]">
                <span class="font-medium text-[color:var(--nx-text)]">Untersuchungen</span> — der Katalog arbeitsmedizinischer
                Untersuchungen (DGUV-Grundsätze). Erbrachte Leistungen (Sprechstunde → Termin) referenzieren einen Eintrag.
            </p>
        </x-nx-card>
    </x-ui-page-container>
</x-ui-page>
