<?php

/**
 * ArbMedVV – web routes
 *
 * Prefix (from config): /examinations
 */

use Platform\Examinations\Livewire\Dashboard;
use Platform\Examinations\Livewire\Examination\Index as ExaminationIndex;
use Platform\Examinations\Livewire\Examination\Show as ExaminationShow;

// Dashboard – catalog overview
Route::get('/', Dashboard::class)->name('examinations.dashboard');

// Examination catalog
Route::get('/examinations', ExaminationIndex::class)->name('examinations.examinations.index');
Route::get('/examinations/{examination}', ExaminationShow::class)->name('examinations.examinations.show');
