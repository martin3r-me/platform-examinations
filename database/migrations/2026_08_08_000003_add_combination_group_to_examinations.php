<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            // Vermengungs-/Exklusivitätsgruppe (orthogonal zur category): z.B. "vorsorge" | "eignung".
            // Regel (durchgesetzt im Termin/Bescheinigung): nur EINE nicht-leere Gruppe je Termin.
            $table->string('combination_group')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn('combination_group');
        });
    }
};
