<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Igrac extends Model
{
    protected $table = 'igrac';
    public $timestamps = false;       // tablica nema created_at/updated_at
    protected $fillable = ['ime', 'prezime', 'datum_rodenja', 'broj_licence', 'id_klub'];

    public function klub()
    {
        return $this->belongsTo(Klub::class, 'id_klub');
    }
}
