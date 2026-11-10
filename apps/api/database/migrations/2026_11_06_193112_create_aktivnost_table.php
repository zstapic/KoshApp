<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aktivnost', function (Blueprint $table) {
            $table->uuid("id")->primary()->comment("UUID v4 koji generira tablet");
            $table->integer("redni_broj");
            $table->foreignId("id_utakmica")->constrained("utakmica");
            $table->enum("tip", [
                "Kos_1", "Kos_2", "Kos_3", "Osobna_P", "Tehnicka_T", "Nesportska_U",
                "Diskvalifikacijska_D", "Tehnicka_C", "Tehnicka_B", "Zamjena",
                "Timeout", "HCC", "Zavrsi_cetvrtinu",
            ]);
            $table->foreignId("id_igrac")->nullable()->constrained("igrac");
            $table->foreignId("id_trener")->nullable()->constrained("trener");
            $table->foreignId("id_selekcija")->nullable()->constrained("selekcija");
            $table->foreignId("id_igrac_izlazi")->nullable()->constrained("igrac");
            $table->integer("cetvrtina");
            $table->integer("minuta")->nullable();
            $table->dateTime("kreirano");
            $table->boolean("ponisten")->default(false);

            $table->unique(["id_utakmica", "redni_broj"], "aktivnost_utakmica_redni_broj_unique");
        });

        // Schema builder nema CHECK, a SQLite (testovi) ne podrzava ALTER TABLE ADD CONSTRAINT.
        if (in_array(DB::getDriverName(), ["mysql", "mariadb"])) {
            DB::statement("ALTER TABLE aktivnost ADD CONSTRAINT aktivnost_igrac_ili_trener CHECK (id_igrac IS NULL OR id_trener IS NULL)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivnost');
    }
};
