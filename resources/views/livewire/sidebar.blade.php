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
        <x-ui-sidebar-item :href="route('examinations.bundles.index')" :active="request()->routeIs('examinations.bundles.*')">
            @svg('heroicon-o-squares-2x2', 'w-4 h-4 text-[var(--nx-text)]')
            <span class="ml-2 text-sm">Bündel</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    {{-- Kategorien liegen bewusst NICHT hier, sondern als seitenspezifischer Filter in der
         inneren Sidebar (Katalog/Dashboard) — sonst dreifache Redundanz (Modul-Nav + innere Sidebar + Dropdown). --}}
</div>
