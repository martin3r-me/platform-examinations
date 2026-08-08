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
];
