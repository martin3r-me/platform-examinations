<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Standard-Intervalle (Recall) am Grundsatz — getrennt nach Erst- und Folgeuntersuchung.
 * Am Termin wird daraus (Grundsatz + Art + erst/folge) die nächste Fälligkeit vorbelegt,
 * ärztlich überschreibbar. Alter-abhängige Staffelung ist ein späterer Verfeinerungspunkt.
 *
 * ACHTUNG: die geseedeten Monate sind ENTWURF/Orientierung und fachlich von Dr. Erren zu
 * bestätigen. Idempotent (Spalten-Guard; Seed nur wo noch leer).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('examinations', 'interval_first_months')) {
            Schema::table('examinations', function (Blueprint $table) {
                $table->unsignedInteger('interval_first_months')->nullable()->after('regulation_label');
                $table->unsignedInteger('interval_followup_months')->nullable()->after('interval_first_months');
            });
        }

        // Entwurfs-Intervalle [erst, folge] in Monaten — nur für die geläufigen Grundsätze.
        $draft = [
            'dguv:g20' => [12, 36],  // Lärm
            'dguv:g24' => [12, 24],  // Gefährdung der Haut
            'dguv:g26' => [12, 24],  // Atemschutz
            'dguv:g30' => [12, 24],  // Hitze
            'dguv:g35' => [12, 24],  // Ausland
            'dguv:g37' => [36, 60],  // Bildschirm
            'dguv:g41' => [24, 36],  // Absturz
            'dguv:g42' => [12, 36],  // Infektionsgefährdung
            'dguv:g46' => [12, 36],  // Muskel-Skelett
        ];

        foreach ($draft as $code => [$first, $follow]) {
            DB::table('examinations')
                ->where('catalog_code', $code)
                ->whereNull('interval_first_months')
                ->update([
                    'interval_first_months'    => $first,
                    'interval_followup_months' => $follow,
                    'updated_at'               => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('examinations', 'interval_first_months')) {
            Schema::table('examinations', function (Blueprint $table) {
                $table->dropColumn(['interval_first_months', 'interval_followup_months']);
            });
        }
    }
};
