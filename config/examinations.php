<?php

return [
    'name'        => 'Untersuchungen',
    'description' => 'Katalog arbeitsmedizinischer Untersuchungen (DGUV-Grundsätze).',
    'version'     => '1.0.0',

    'routing' => [
        'prefix'     => 'examinations',
        'middleware' => ['web', 'auth'],
    ],

    'guard' => 'web',

    'navigation' => [
        'route' => 'examinations.dashboard',
        'icon'  => 'heroicon-o-beaker',
        'order' => 46,
    ],

    'sidebar' => [
        [
            'group' => 'Allgemein',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'examinations.dashboard', 'icon' => 'heroicon-o-home'],
                ['label' => 'Untersuchungen', 'route' => 'examinations.examinations.index', 'icon' => 'heroicon-o-beaker'],
            ],
        ],
    ],

    // Frei erweiterbare Kategorien (Anzeige-Labels deutsch).
    'categories' => [
        'physical'   => 'Physikalische Einwirkungen',
        'hazardous'  => 'Gefahrstoffe',
        'biological' => 'Biologische Arbeitsstoffe',
        'strain'     => 'Belastungen / Tätigkeiten',
        'other'      => 'Sonstige',
    ],

    // Vermengungs-/Exklusivitätsgruppen (orthogonal zur Kategorie). Frei erweiterbar.
    // Regel (im Termin/Bescheinigung durchgesetzt): nur EINE nicht-leere Gruppe je Termin.
    // Gesetzlicher Kernfall: Vorsorge (z.B. G20) und Eignung (z.B. G25) nicht mischbar.
    'combination_groups' => [
        'vorsorge' => 'Vorsorge',
        'eignung'  => 'Eignung',
    ],
];
