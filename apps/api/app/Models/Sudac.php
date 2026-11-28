<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sudac extends Model
{
    protected $table = 'sudac';
    public $timestamps = false;       // tablica nema created_at/updated_at
    protected $fillable = ['ime', 'prezime', 'id_savez'];
}
