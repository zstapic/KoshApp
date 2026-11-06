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
        Schema::create('ekipa_u_ligi', function (Blueprint $table) {
            $table->foreignId("id_izdanje_lige")->constrained("izdanje_lige");
            $table->foreignId("id_selekcija")->constrained("selekcija");

            $table->primary(["id_izdanje_lige", "id_selekcija"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekipa_u_ligi');
    }
};
