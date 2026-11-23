<?php

// Minimalni model za tablicu "selekcija" – za sada služi samo za provjeru koristi li se kategorija.
// Proširiti (fillable, relacije) u ticketu za selekcije.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Selekcija extends Model
{
    protected $table = 'selekcija';
    public $timestamps = false;
}
