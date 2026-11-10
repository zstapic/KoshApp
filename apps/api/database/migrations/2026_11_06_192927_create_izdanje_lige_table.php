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
        Schema::create('izdanje_lige', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_liga")->constrained("liga");
            $table->foreignId("id_sezona")->constrained("sezona");

            $table->unique(["id_liga", "id_sezona"], "izdanje_lige_liga_sezona_unique");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izdanje_lige');
    }
};
