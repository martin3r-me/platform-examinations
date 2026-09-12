<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\UuidV7;

/**
 * Verfahren-Katalog · Ausbaustufe 5 — vollständiger DGUV-/G-Grundsatzsatz (Vorsorge).
 *
 * Legt die 39 noch fehlenden klassischen Grundsätze an (G 1.1 – G 46, ohne die bereits
 * vorhandenen G20/24/25/26/30/35/37/41/42/46). Quelle: publizierte G-Grundsatz-Liste.
 * Alle category_kind='vorsorge'. Titel = klassische G-Titel; neue DGUV-Empfehlungs-Namen +
 * Anhang-6-Alias werden separat nachgepflegt.
 *
 * Feldnamen englisch, Werte/Titel deutsch. Idempotent per catalog_code, pro provisioniertem Team.
 */
return new class extends Migration
{
    public function up(): void
    {
        // [catalog_code, number, title]
        $g = [
            ['dguv:g1-1', 'G 1.1', 'Silikogener Staub'],
            ['dguv:g1-2', 'G 1.2', 'Asbestfaserhaltiger Staub'],
            ['dguv:g1-3', 'G 1.3', 'Künstliche Mineralfasern (Kat. 1/2)'],
            ['dguv:g1-4', 'G 1.4', 'Allgemeiner Staub'],
            ['dguv:g2',  'G 2',  'Blei oder seine Verbindungen'],
            ['dguv:g3',  'G 3',  'Bleialkyle'],
            ['dguv:g4',  'G 4',  'Gefahrstoffe, die Hautkrebs hervorrufen'],
            ['dguv:g5',  'G 5',  'Glykoldinitrat oder Glycerinnitrat'],
            ['dguv:g6',  'G 6',  'Schwefelkohlenstoff'],
            ['dguv:g7',  'G 7',  'Kohlenmonoxid'],
            ['dguv:g8',  'G 8',  'Benzol'],
            ['dguv:g9',  'G 9',  'Quecksilber oder seine Verbindungen'],
            ['dguv:g10', 'G 10', 'Methanol'],
            ['dguv:g11', 'G 11', 'Schwefelwasserstoff'],
            ['dguv:g12', 'G 12', 'Phosphor (weiß)'],
            ['dguv:g13', 'G 13', 'Tetrachlormethan'],
            ['dguv:g14', 'G 14', 'Trichlorethen'],
            ['dguv:g15', 'G 15', 'Chrom-VI-Verbindungen'],
            ['dguv:g16', 'G 16', 'Arsen oder seine Verbindungen'],
            ['dguv:g17', 'G 17', 'Tetrachlorethen'],
            ['dguv:g18', 'G 18', '1,1,2,2-Tetrachlorethan / Perchlorethan'],
            ['dguv:g19', 'G 19', 'Dimethylformamid'],
            ['dguv:g21', 'G 21', 'Kälte'],
            ['dguv:g22', 'G 22', 'Säureschäden der Zähne'],
            ['dguv:g23', 'G 23', 'Obstruktive Atemwegserkrankungen'],
            ['dguv:g27', 'G 27', 'Isocyanate'],
            ['dguv:g28', 'G 28', 'Monochlormethan'],
            ['dguv:g29', 'G 29', 'Xylol / Toluol'],
            ['dguv:g31', 'G 31', 'Überdruck'],
            ['dguv:g32', 'G 32', 'Cadmium und seine Verbindungen'],
            ['dguv:g33', 'G 33', 'Aromatische Nitro- und Aminoverbindungen'],
            ['dguv:g34', 'G 34', 'Fluor und seine anorganischen Verbindungen'],
            ['dguv:g36', 'G 36', 'Vinylchlorid'],
            ['dguv:g38', 'G 38', 'Nickelstäube'],
            ['dguv:g39', 'G 39', 'Schweißrauche'],
            ['dguv:g40', 'G 40', 'Krebserzeugende und erbgutverändernde Gefahrstoffe'],
            ['dguv:g43', 'G 43', 'Biotechnologie'],
            ['dguv:g44', 'G 44', 'Hartholzstäube'],
            ['dguv:g45', 'G 45', 'Styrol'],
        ];

        $teamIds = DB::table('examinations')->distinct()->pluck('team_id');

        foreach ($teamIds as $teamId) {
            $maxPos = (int) DB::table('examinations')->where('team_id', $teamId)->max('position');

            foreach ($g as [$code, $number, $title]) {
                $exists = DB::table('examinations')
                    ->where('team_id', $teamId)
                    ->where('catalog_code', $code)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('examinations')->insert([
                    'uuid'              => (string) UuidV7::generate(),
                    'catalog_code'      => $code,
                    'team_id'           => $teamId,
                    'number'            => $number,
                    'title'             => $title,
                    'category'          => null,
                    'combination_group' => 'vorsorge',
                    'category_kind'     => 'vorsorge',
                    'legal_basis'       => 'DGUV Grundsatz ' . $number,
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
        $codes = [
            'dguv:g1-1','dguv:g1-2','dguv:g1-3','dguv:g1-4','dguv:g2','dguv:g3','dguv:g4','dguv:g5',
            'dguv:g6','dguv:g7','dguv:g8','dguv:g9','dguv:g10','dguv:g11','dguv:g12','dguv:g13',
            'dguv:g14','dguv:g15','dguv:g16','dguv:g17','dguv:g18','dguv:g19','dguv:g21','dguv:g22',
            'dguv:g23','dguv:g27','dguv:g28','dguv:g29','dguv:g31','dguv:g32','dguv:g33','dguv:g34',
            'dguv:g36','dguv:g38','dguv:g39','dguv:g40','dguv:g43','dguv:g44','dguv:g45',
        ];
        DB::table('examinations')->whereIn('catalog_code', $codes)->delete();
    }
};
