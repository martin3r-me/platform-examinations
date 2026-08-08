{{-- Modul-Nav-Sidebar (nx) — Untersuchungs-Katalog --}}
<div>
    <div x-show="!collapsed" class="p-3 text-sm italic text-[var(--nx-text)] uppercase border-b border-[color:var(--nx-line)] mb-2">
        Untersuchungen
    </div>

    <x-ui-sidebar-list label="Katalog">
        <x-ui-sidebar-item :href="route('examinations.dashboard')" :active="request()->routeIs('examinations.dashboard')">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--nx-text)]')
            <span class="ml-2 text-sm">Dashboard</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('examinations.examinations.index')" :active="request()->routeIs('examinations.examinations.*')">
            @svg('heroicon-o-beaker', 'w-4 h-4 text-[var(--nx-text)]')
            <span class="ml-2 text-sm">Untersuchungen</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    @if(!empty($categories))
        <x-ui-sidebar-list label="Kategorien">
            @foreach($categories as $key => $label)
                <x-ui-sidebar-item :href="route('examinations.examinations.index', ['filterCategory' => $key])">
                    @svg('heroicon-o-folder', 'w-4 h-4 text-[var(--nx-text)]')
                    <span class="ml-2 text-sm flex-1 truncate">{{ $label }}</span>
                    @if(($categoryCounts[$key] ?? 0) > 0)
                        <span class="text-xs text-[color:var(--nx-faint)]">{{ $categoryCounts[$key] }}</span>
                    @endif
                </x-ui-sidebar-item>
            @endforeach
        </x-ui-sidebar-list>
    @endif
</div>
