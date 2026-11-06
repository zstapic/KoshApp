<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('utakmica_trener', function (Blueprint $table) {
            $table->foreignId("id_utakmica")->constrained("utakmica");
            $table->foreignId("id_selekcija")->constrained("selekcija");
            $table->foreignId("id_trener")->constrained("trener");
            $table->enum("uloga", ["Glavni", "Pomocni"]);

            $table->primary(["id_utakmica", "id_selekcija", "uloga"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utakmica_trener');
    }
};
