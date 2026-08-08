<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * examinations — Katalog arbeitsmedizinischer UNTERSUCHUNGEN (DGUV-Grundsätze G1–G46 u.a.).
 * Referenz-Schicht (wie arbmedvv): eine erbrachte Leistung (encounter Service) bindet per
 * morphMap (catalog_type='examination', catalog_id) an einen Katalog-Eintrag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('team_id')->index();

            $table->string('number', 32)->nullable();      // z.B. "G 20", "G 37"
            $table->string('title');                        // z.B. "Lärm"
            $table->string('category')->nullable();         // Gruppierung (z.B. "Physikalische Einwirkungen")
            $table->text('description')->nullable();
            $table->string('legal_basis')->nullable();      // z.B. "DGUV Grundsatz G 20"

            $table->string('status', 16)->default('active'); // active|archived

            // Versionierung / Gültigkeit (für Novellierungen), analog arbmedvv.
            $table->unsignedInteger('version')->default(1);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('regulation_label')->nullable(); // z.B. "DGUV Stand 2023"

            $table->unsignedInteger('position')->default(0);
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'status']);
            $table->index(['team_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
