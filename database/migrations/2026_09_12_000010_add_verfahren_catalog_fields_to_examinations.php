<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verfahren-Katalog · Ausbaustufe 1 — macht examinations sync-fähig und kategorisiert.
 *
 * - catalog_code: kanonischer, team-ÜBERGREIFENDER Master-Schlüssel (wie LOINC bei observation).
 *   Grundlage für den späteren Master→Praxis-Sync; die team-lokale `uuid` taugt dafür NICHT
 *   (pro Team anders). Ohne ihn kann kein Sync "denselben" Grundsatz über mehrere Praxen zuordnen.
 * - category_kind: gesetzliche Kategorie (vorsorge|eignung|fev) — die "eine Kategorie je Vorgang"
 *   (DGUV/ArbMedVV-Trennung). Orthogonal zu `category` (Fachgebiet) und zu `combination_group`
 *   (Vermengungsgruppe), das daraus später ableitbar ist.
 *
 * Rein additiv, beide Spalten nullable → keine Auswirkung auf Bestandsdaten (Backfill in Phase 1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->string('catalog_code', 64)->nullable()->after('uuid');
            $table->string('category_kind', 16)->nullable()->after('combination_group'); // vorsorge|eignung|fev

            $table->index(['team_id', 'catalog_code'], 'examinations_team_catalog_code_idx');
            $table->index(['team_id', 'category_kind'], 'examinations_team_category_kind_idx');
        });
    }

    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropIndex('examinations_team_catalog_code_idx');
            $table->dropIndex('examinations_team_category_kind_idx');
            $table->dropColumn(['catalog_code', 'category_kind']);
        });
    }
};
