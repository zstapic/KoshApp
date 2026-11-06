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
        Schema::create('utakmica_sastav', function (Blueprint $table) {
            $table->foreignId("id_utakmica")->constrained("utakmica");
            $table->foreignId("id_igrac")->constrained("igrac");
            $table->foreignId("id_selekcija")->constrained("selekcija");
            $table->integer("broj_dresa");
            $table->boolean("kapetan")->default(false);
            $table->boolean("u_prvoj_petorci")->default(false);

            $table->primary(["id_utakmica", "id_igrac"]);
            $table->unique(["id_utakmica", "id_selekcija", "broj_dresa"], "utakmica_sastav_dres_unique");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utakmica_sastav');
    }
};
