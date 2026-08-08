<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_bundles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active | archived
            $table->integer('position')->default(0);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Bündel → Untersuchungen (geordnet). Beim Erfassen im Termin werden die Einzelnen angelegt.
        Schema::create('examination_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_id')->index();
            $table->unsignedBigInteger('examination_id')->index();
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->unique(['bundle_id', 'examination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_bundle_items');
        Schema::dropIfExists('examination_bundles');
    }
};
