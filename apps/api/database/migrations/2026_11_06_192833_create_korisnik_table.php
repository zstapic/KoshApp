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
        Schema::create('korisnik', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_savez")->nullable()->comment("NULL samo za SuperAdmin")->constrained("savez");
            $table->string("korisnicko_ime");
            $table->string("lozinka_hash");
            $table->enum("uloga", ["AdminSaveza", "Zapisnicar", "SuperAdmin"]);
            $table->boolean("aktivan")->default(true);

            $table->unique(["korisnicko_ime"], "korisnik_korisnicko_ime_unique");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('korisnik');
    }
};
