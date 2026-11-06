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
        Schema::create('nastupa_za_selekciju', function (Blueprint $table) {
            $table->foreignId("id_igrac")->constrained("igrac");
            $table->foreignId("id_selekcija")->constrained("selekcija");
            $table->foreignId("id_sezona")->constrained("sezona");

            $table->primary(["id_igrac", "id_selekcija", "id_sezona"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nastupa_za_selekciju');
    }
};
