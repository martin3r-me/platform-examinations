<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Verfahren-Katalog · Ausbaustufe 6 — neue DGUV-Empfehlungs-Namen + Korrekturen.
 *
 * Quelle: OFFIZIELLE DGUV-Zuordnungstabelle "Zuordnung DGUV Empfehlungen – abgelöste DGUV
 * Grundsätze" (liste-zuordnung.pdf). Sie korrigiert Fehler des vorigen Seeds (Drittquelle):
 *   - G 13 = Platinverbindungen (NICHT Tetrachlormethan)
 *   - G 17 = Künstliche optische Strahlung (NICHT Tetrachlorethen)
 *   - G 28 = Arbeiten in sauerstoffreduzierter Atmosphäre (NICHT Monochlormethan)
 *   - G 18 / G 22 / G 43 nicht in der aktuellen Liste → archiviert.
 *
 * Setzt je Grundsatz: title = bisheriger Grundsatz-Titel (korrekt), recommendation_name = neue
 * DGUV-Empfehlungs-Bezeichnung. So sind beide Bezeichnungen (G-Nr + alter + neuer Name) durchsuchbar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('examinations', 'recommendation_name')) {
            Schema::table('examinations', function (Blueprint $table) {
                $table->string('recommendation_name')->nullable()->after('title');
            });
        }

        // "G x" => [bisheriger Grundsatz-Titel (legacy), neue DGUV-Empfehlungs-Bezeichnung]
        $map = [
            'G 1.1' => ['Mineralischer Staub, Teil 1: Silikogener Staub', 'Silikogener Staub'],
            'G 1.2' => ['Mineralischer Staub, Teil 2: Asbestfaserhaltiger Staub', 'Asbest'],
            'G 1.3' => ['Mineralischer Staub, Teil 3: Künstlicher mineralischer Faserstaub (Kat. 1A/1B)', 'Tätigkeiten mit Hochtemperaturwollen (Faserstäube Kat. 1A/1B)'],
            'G 1.4' => ['Staubbelastung', 'Staubbelastung'],
            'G 2'   => ['Blei oder seine Verbindungen (außer Bleialkyle)', 'Blei und anorganische Bleiverbindungen'],
            'G 3'   => ['Bleialkyle', 'Bleitetraethyl und Bleitetramethyl'],
            'G 4'   => ['Gefahrstoffe, die Hautkrebs hervorrufen', 'Polycyclische aromatische Kohlenwasserstoffe (PAK)'],
            'G 5'   => ['Glykoldinitrat oder Glycerintrinitrat', 'Glycerintrinitrat (Nitroglycerin) und Glykoldinitrat (Nitroglykol)'],
            'G 6'   => ['Kohlenstoffdisulfid (Schwefelkohlenstoff)', 'Kohlenstoffdisulfid (Schwefelkohlenstoff)'],
            'G 7'   => ['Kohlenmonoxid', 'Kohlenmonoxid'],
            'G 8'   => ['Benzol', 'Benzol'],
            'G 9'   => ['Quecksilber oder seine Verbindungen', 'Quecksilber und anorganische Quecksilberverbindungen'],
            'G 10'  => ['Methanol', 'Methanol'],
            'G 11'  => ['Schwefelwasserstoff', 'Schwefelwasserstoff'],
            'G 12'  => ['Phosphor (weißer)', 'Weißer Phosphor'],
            'G 13'  => ['Chloroplatinate', 'Platinverbindungen'],
            'G 14'  => ['Trichlorethen und andere Chlorkohlenwasserstoff-Lösungsmittel', 'Trichlorethen, Tetrachlorethen und Dichlormethan'],
            'G 15'  => ['Chrom-(VI)-Verbindungen', 'Chrom-(VI)-Verbindungen'],
            'G 16'  => ['Arsen oder seine Verbindungen (außer Arsenwasserstoff)', 'Arsen und Arsenverbindungen'],
            'G 17'  => ['Künstliche optische Strahlung', 'Künstliche optische Strahlung'],
            'G 19'  => ['Dimethylformamid', 'Dimethylformamid'],
            'G 20'  => ['Lärm', 'Lärm'],
            'G 21'  => ['Kältearbeiten', 'Kältearbeiten'],
            'G 23'  => ['Obstruktive Atemwegserkrankungen', 'Tätigkeiten mit Stoffen, die obstruktive Atemwegserkrankungen auslösen können'],
            'G 24'  => ['Hauterkrankungen (außer Hautkrebs)', 'Gefährdung der Haut'],
            'G 25'  => ['Fahr-, Steuer- und Überwachungstätigkeiten', 'Fahr-, Steuer- und Überwachungstätigkeiten'],
            'G 26'  => ['Atemschutzgeräte', 'Atemschutzgeräte'],
            'G 27'  => ['Isocyanate', 'Isocyanate'],
            'G 28'  => ['Arbeiten in sauerstoffreduzierter Atmosphäre', 'Arbeiten in sauerstoffreduzierter Atmosphäre'],
            'G 29'  => ['Toluol und Xylol', 'Toluol und Xylol'],
            'G 30'  => ['Hitzearbeiten', 'Hitzearbeiten'],
            'G 31'  => ['Überdruck', 'Überdruck (Arbeiten in Druckluft und Taucherarbeiten)'],
            'G 32'  => ['Cadmium oder seine Verbindungen', 'Cadmium und Cadmiumverbindungen'],
            'G 33'  => ['Aromatische Nitro- oder Aminoverbindungen', 'Aromatische Nitro- und Aminoverbindungen'],
            'G 34'  => ['Fluor oder seine anorganischen Verbindungen', 'Fluor und anorganische Fluorverbindungen'],
            'G 35'  => ['Arbeitsaufenthalt im Ausland unter besonderen klimatischen und gesundheitlichen Belastungen', 'Arbeitsaufenthalt im Ausland unter besonderen klimatischen oder gesundheitlichen Belastungen'],
            'G 36'  => ['Vinylchlorid', 'Vinylchlorid'],
            'G 37'  => ['Bildschirmarbeitsplätze', 'Tätigkeiten an Bildschirmgeräten'],
            'G 38'  => ['Nickel oder seine Verbindungen', 'Nickel und Nickelverbindungen'],
            'G 39'  => ['Schweißrauche', 'Schweißen und Trennen von Metallen'],
            'G 40'  => ['Krebserzeugende und erbgutverändernde Gefahrstoffe – allgemein', 'Krebserzeugende und keimzellmutagene Gefahrstoffe – allgemein'],
            'G 41'  => ['Arbeiten mit Absturzgefahr', 'Arbeiten mit Absturzgefahr'],
            'G 42'  => ['Tätigkeiten mit Infektionsgefährdung', 'Tätigkeiten mit Infektionsgefährdung'],
            'G 44'  => ['Hartholzstäube', 'Hartholzstaub'],
            'G 45'  => ['Styrol', 'Styrol'],
            'G 46'  => ['Belastungen des Muskel- und Skelettsystems einschließlich Vibrationen', 'Belastungen des Muskel-Skelett-Systems einschließlich Vibrationen'],
        ];

        foreach ($map as $number => [$legacyTitle, $recommendation]) {
            DB::table('examinations')
                ->whereRaw("REPLACE(number, ' ', '') = ?", [str_replace(' ', '', $number)])
                ->update([
                    'title'               => $legacyTitle,
                    'recommendation_name' => $recommendation,
                    'updated_at'          => now(),
                ]);
        }

        // Nicht in der aktuellen DGUV-Liste (abgelöst/historisch) → archivieren.
        DB::table('examinations')
            ->whereIn('catalog_code', ['dguv:g18', 'dguv:g22', 'dguv:g43'])
            ->update(['status' => 'archived', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('examinations')
            ->whereIn('catalog_code', ['dguv:g18', 'dguv:g22', 'dguv:g43'])
            ->update(['status' => 'active']);

        if (Schema::hasColumn('examinations', 'recommendation_name')) {
            Schema::table('examinations', function (Blueprint $table) {
                $table->dropColumn('recommendation_name');
            });
        }
    }
};
