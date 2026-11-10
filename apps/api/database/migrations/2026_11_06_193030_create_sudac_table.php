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
        Schema::create('sudac', function (Blueprint $table) {
            $table->id();
            $table->string("ime");
            $table->string("prezime");
            $table->foreignId("id_savez")->constrained("savez");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sudac');
    }
};
