<?php

// Model predstavlja tablicu u bazi podataka i omogućuje interakciju s njom. U ovom slučaju, model "Klub" predstavlja tablicu "klub" u bazi podataka.
// i onda ClubController koristi ovaj model za dohvat podataka iz tablice "klub" i vraćanje tih podataka kao JSON odgovora na zahtjeve prema API-ju.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klub extends Model
{
    protected $table = 'klub';
    public $timestamps = false;       // tablica nema created_at/updated_at
    protected $fillable = ['naziv', 'id_savez'];

}
