<?php

// Minimalni model za tablicu "liga" – za sada služi samo za provjeru koristi li se kategorija.
// Proširiti (fillable, relacije) u ticketu za lige.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liga extends Model
{
    protected $table = 'liga';
    public $timestamps = false;
}
