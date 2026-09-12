<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\UuidV7;

/**
 * Verfahren-Katalog · Ausbaustufe 2 — DGUV-Grundsatz-Grundstock (Kategorie Vorsorge).
 *
 * 1) Backfillt die 5 vorhandenen Verfahren (catalog_code + category_kind='vorsorge').
 * 2) Ergänzt die Grundsätze, für die es heute Anamnese-Fragen/Anlässe gibt, aber noch KEIN
 *    Verfahren:
 *      - G 30  Hitzearbeiten                              (Herz-Kreislauf/Hitze)
 *      - G 35  Arbeitsaufenthalt im Ausland (Tropen)      (Reisemedizin)
 *      - G 42  Tätigkeiten mit Infektionsgefährdung        (FSME / Impfstatus)
 *      - G 46  Muskel-/Skelettsystem inkl. Vibrationen     (Bewegungsapparat + Hand-Arm/Vibration)
 *
 * FACHLICH von Dr. Erren zu bestätigen/verfeinern (Nummern/Titel/Zuschnitt). Dies ist die
 * Keimzelle des späteren globalen Masters. Idempotent; nur für bereits provisionierte Teams.
 */
return new class extends Migration
{
    public function up(): void
    {
        $grundsaetze = [
            // Bestand (werden per Nummer getroffen und nur backfillt)
            ['code' => 'dguv:g20', 'number' => 'G 20', 'title' => 'Lärm',                                'category' => 'physical',   'legal_basis' => 'DGUV Grundsatz G 20'],
            ['code' => 'dguv:g24', 'number' => 'G 24', 'title' => 'Hauterkrankungen (außer Hautkrebs)',  'category' => 'hazardous',  'legal_basis' => 'DGUV Grundsatz G 24'],
            ['code' => 'dguv:g26', 'number' => 'G 26', 'title' => 'Atemschutzgeräte',                    'category' => 'strain',     'legal_basis' => 'DGUV Grundsatz G 26'],
            ['code' => 'dguv:g37', 'number' => 'G 37', 'title' => 'Bildschirmarbeitsplätze',             'category' => 'other',      'legal_basis' => 'DGUV Grundsatz G 37'],
            ['code' => 'dguv:g41', 'number' => 'G 41', 'title' => 'Arbeiten mit Absturzgefahr',          'category' => 'strain',     'legal_basis' => 'DGUV Grundsatz G 41'],
            // Neu
            ['code' => 'dguv:g30', 'number' => 'G 30', 'title' => 'Hitzearbeiten',                       'category' => 'physical',   'legal_basis' => 'DGUV Grundsatz G 30'],
            ['code' => 'dguv:g35', 'number' => 'G 35', 'title' => 'Arbeitsaufenthalt im Ausland unter besonderen klimatischen und gesundheitlichen Belastungen', 'category' => 'other', 'legal_basis' => 'DGUV Grundsatz G 35'],
            ['code' => 'dguv:g42', 'number' => 'G 42', 'title' => 'Tätigkeiten mit Infektionsgefährdung', 'category' => 'biological', 'legal_basis' => 'DGUV Grundsatz G 42'],
            ['code' => 'dguv:g46', 'number' => 'G 46', 'title' => 'Belastungen des Muskel- und Skelettsystems einschließlich Vibrationen', 'category' => 'physical', 'legal_basis' => 'DGUV Grundsatz G 46'],
        ];

        // Nur bereits provisionierte Teams (die schon einen Katalog haben) — kein Seeding leerer Teams.
        $teamIds = DB::table('examinations')->distinct()->pluck('team_id');

        foreach ($teamIds as $teamId) {
            $maxPos = (int) DB::table('examinations')->where('team_id', $teamId)->max('position');

            foreach ($grundsaetze as $g) {
                // Vorhandenes Verfahren per Nummer treffen (leerzeichen-tolerant: "G 20" == "G20").
                $existing = DB::table('examinations')
                    ->where('team_id', $teamId)
                    ->whereRaw("REPLACE(number, ' ', '') = ?", [str_replace(' ', '', $g['number'])])
                    ->first();

                if ($existing) {
                    DB::table('examinations')->where('id', $existing->id)->update([
                        'catalog_code'  => $existing->catalog_code ?: $g['code'],
                        'category_kind' => $existing->category_kind ?: 'vorsorge',
                        'updated_at'    => now(),
                    ]);
                    continue;
                }

                // Fehlendes Verfahren neu anlegen.
                DB::table('examinations')->insert([
                    'uuid'              => (string) UuidV7::generate(),
                    'catalog_code'      => $g['code'],
                    'team_id'           => $teamId,
                    'number'            => $g['number'],
                    'title'             => $g['title'],
                    'category'          => $g['category'],
                    'combination_group' => 'vorsorge',
                    'category_kind'     => 'vorsorge',
                    'legal_basis'       => $g['legal_basis'],
                    'status'            => 'active',
                    'version'           => 1,
                    'position'          => ++$maxPos,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Nur die NEU angelegten Grundsätze zurücknehmen; die Bestands-5 bleiben (deren
        // catalog_code/category_kind-Backfill ist harmlos und wird stehen gelassen).
        DB::table('examinations')->whereIn('catalog_code', [
            'dguv:g30', 'dguv:g35', 'dguv:g42', 'dguv:g46',
        ])->delete();
    }
};
