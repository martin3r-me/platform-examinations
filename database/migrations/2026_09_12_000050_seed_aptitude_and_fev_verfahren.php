<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\UuidV7;

/**
 * Verfahren-Katalog · Ausbaustufe 4 — Grundstock Eignung + FeV.
 *
 * Legt die etablierten Eignungs- (category_kind='eignung') und FeV-Verfahren
 * (category_kind='fev') an. STARTER-Satz, fachlich von Dr. Erren zu vervollständigen —
 * NICHT der vollständige DGUV-Katalog.
 *
 * Anders als Vorsorge haben Eignung/FeV KEINE arbmedvv-Anlässe (Auslöser = Tätigkeit bzw.
 * Führerschein-Klasse). Am Termin nutzbar erst mit direktem Verfahren-Zugang (UI-Schritt).
 *
 * Feldnamen englisch; Werte (category_kind, Titel) in der Fachsprache. Idempotent per
 * catalog_code, pro provisioniertem Team.
 */
return new class extends Migration
{
    public function up(): void
    {
        $verfahren = [
            // Eignung (DGUV Empfehlungen Kap. 2.2)
            ['code' => 'dguv:g25',                'number' => 'G 25', 'title' => 'Fahr-, Steuer- und Überwachungstätigkeiten', 'kind' => 'eignung', 'group' => 'eignung', 'legal_basis' => 'DGUV Empfehlung (ehem. G 25)'],
            ['code' => 'dguv:absturz-eignung',    'number' => null,   'title' => 'Absturzgefahr / Höhentauglichkeit (Eignung)',  'kind' => 'eignung', 'group' => 'eignung', 'legal_basis' => 'DGUV Empfehlung Eignungsbeurteilung'],
            ['code' => 'dguv:atemschutz-eignung', 'number' => null,   'title' => 'Atemschutzgeräte (Eignung)',                   'kind' => 'eignung', 'group' => 'eignung', 'legal_basis' => 'DGUV Empfehlung Eignungsbeurteilung'],

            // FeV (Fahrerlaubnis-Verordnung)
            ['code' => 'fev:anlage5-c',            'number' => null, 'title' => 'Fahrerlaubnis Klassen C/C1/CE/C1E (LKW) – Anlage 5 FeV', 'kind' => 'fev', 'group' => null, 'legal_basis' => 'FeV Anlage 5'],
            ['code' => 'fev:anlage5-d',            'number' => null, 'title' => 'Fahrerlaubnis Klassen D/D1/DE/D1E (Bus) – Anlage 5 FeV', 'kind' => 'fev', 'group' => null, 'legal_basis' => 'FeV Anlage 5'],
            ['code' => 'fev:fahrgastbefoerderung', 'number' => null, 'title' => 'Fahrgastbeförderung – Anlage 5 FeV',                    'kind' => 'fev', 'group' => null, 'legal_basis' => 'FeV Anlage 5'],
            ['code' => 'fev:anlage6-sehvermoegen', 'number' => null, 'title' => 'Sehvermögen – Anlage 6 FeV',                           'kind' => 'fev', 'group' => null, 'legal_basis' => 'FeV Anlage 6'],
        ];

        $teamIds = DB::table('examinations')->distinct()->pluck('team_id');

        foreach ($teamIds as $teamId) {
            $maxPos = (int) DB::table('examinations')->where('team_id', $teamId)->max('position');

            foreach ($verfahren as $v) {
                $exists = DB::table('examinations')
                    ->where('team_id', $teamId)
                    ->where('catalog_code', $v['code'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('examinations')->insert([
                    'uuid'              => (string) UuidV7::generate(),
                    'catalog_code'      => $v['code'],
                    'team_id'           => $teamId,
                    'number'            => $v['number'],
                    'title'             => $v['title'],
                    'category'          => null,
                    'combination_group' => $v['group'],
                    'category_kind'     => $v['kind'],
                    'legal_basis'       => $v['legal_basis'],
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
        DB::table('examinations')->whereIn('catalog_code', [
            'dguv:g25', 'dguv:absturz-eignung', 'dguv:atemschutz-eignung',
            'fev:anlage5-c', 'fev:anlage5-d', 'fev:fahrgastbefoerderung', 'fev:anlage6-sehvermoegen',
        ])->delete();
    }
};
