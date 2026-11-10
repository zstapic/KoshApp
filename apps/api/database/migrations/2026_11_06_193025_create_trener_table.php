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
        Schema::create('trener', function (Blueprint $table) {
            $table->id();
            $table->string("ime");
            $table->string("prezime");
            $table->string("broj_licence");
            $table->foreignId("id_klub")->constrained("klub");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trener');
    }
};
