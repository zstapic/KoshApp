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
        Schema::create('dvorana', function (Blueprint $table) {
            $table->id();
            $table->string("naziv");
            $table->string("mjesto");
            $table->foreignId("id_savez")->constrained("savez");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dvorana');
    }
};
