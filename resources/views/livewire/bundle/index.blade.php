{{-- Untersuchungs-Bündel – Liste + Anlegen --}}
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Bündel" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Untersuchungen', 'icon' => 'beaker', 'route' => 'examinations.dashboard'],
            ['label' => 'Bündel'],
        ]">
            <x-nx-button variant="primary" size="sm" wire:click="openCreate">
                @svg('heroicon-o-plus', 'w-4 h-4') <span>Neues Bündel</span>
            </x-nx-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-ui-page-container width="contained" spacing="space-y-6">
        @if($bundles->isEmpty())
            <x-nx-card>
                <x-nx-empty icon="heroicon-o-squares-2x2">
                    Noch keine Bündel. Ein Bündel fasst mehrere Untersuchungen zu einem Paket zusammen.
                    <x-slot name="action">
                        <x-nx-button variant="primary" size="sm" wire:click="openCreate">Bündel anlegen</x-nx-button>
                    </x-slot>
                </x-nx-empty>
            </x-nx-card>
        @else
            <x-nx-card flush class="divide-y divide-[color:var(--nx-line)]">
                @foreach($bundles as $b)
                    <x-nx-list-item
                        :href="route('examinations.bundles.show', $b)"
                        wire:navigate
                        icon="heroicon-o-squares-2x2"
                        :title="$b->name"
                        :subtitle="$b->description"
                        :meta="$b->examinations_count.' Untersuchungen · '.$b->status" />
                @endforeach
            </x-nx-card>
        @endif
    </x-ui-page-container>

    {{-- Anlege-Modal --}}
    <x-nx-modal size="md" wire:model="showCreate">
        <x-slot name="header">Neues Bündel anlegen</x-slot>
        <div class="space-y-4">
            <x-nx-input-text name="form.name" label="Name" wire:model="form.name" placeholder="z.B. Einstellung Metallbau" required />
            <x-nx-input-textarea name="form.description" label="Beschreibung" wire:model="form.description" :rows="3" />
        </div>
        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-nx-button variant="ghost" wire:click="$set('showCreate', false)">Abbrechen</x-nx-button>
                <x-nx-button variant="primary" wire:click="save">Anlegen & bearbeiten</x-nx-button>
            </div>
        </x-slot>
    </x-nx-modal>
</x-ui-page>
