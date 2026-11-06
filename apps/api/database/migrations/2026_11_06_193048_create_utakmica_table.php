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
        Schema::create('utakmica', function (Blueprint $table) {
            $table->id();
            $table->integer("broj_utakmice");
            $table->integer("kolo")->nullable();
            $table->foreignId("id_izdanje_lige")->constrained("izdanje_lige");
            $table->foreignId("id_selekcija_domacin")->constrained("selekcija");
            $table->foreignId("id_selekcija_gost")->constrained("selekcija");
            $table->dateTime("datum_vrijeme");
            $table->foreignId("id_dvorana")->nullable()->constrained("dvorana");
            $table->foreignId("id_glavni_sudac")->nullable()->constrained("sudac");
            $table->foreignId("id_sudac_1")->nullable()->constrained("sudac");
            $table->foreignId("id_sudac_2")->nullable()->constrained("sudac");
            $table->foreignId("id_zapisnicar")->nullable()->comment("dodjeljuje admin")->constrained("korisnik");
            $table->string("pomocni_zapisnicar")->nullable();
            $table->string("mjeritelj_vremena")->nullable();
            $table->string("operater_24s")->nullable();
            $table->enum("status", ["Zakazana", "U_tijeku", "Zavrsena", "Predana"])->default("Zakazana");
            $table->dateTime("pocetak")->nullable();
            $table->unsignedBigInteger("id_token_pokretanja")->nullable()->comment("Sanctum token (uredaj) koji je zapoceo utakmicu");
            $table->dateTime("zavrsetak")->nullable();
            $table->integer("rezultat_domacin")->nullable()->comment("upisuje se pri zavrsetku, iz dogadaja");
            $table->integer("rezultat_gost")->nullable()->comment("upisuje se pri zavrsetku, iz dogadaja");
            $table->boolean("prosvjed")->default(false);
            $table->text("prosvjed_napomena")->nullable();

            $table->unique(["id_izdanje_lige", "broj_utakmice"], "utakmica_izdanje_broj_unique");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utakmica');
    }
};
