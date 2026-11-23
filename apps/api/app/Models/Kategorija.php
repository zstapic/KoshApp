<?php

// Model za tablicu "kategorija" – dobne kategorije saveza (npr. U12, U14, Seniori).
// max_dob = najveća dob igrača u kategoriji; NULL znači da nema gornje granice (seniori).
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategorija extends Model
{
    protected $table = 'kategorija';
    public $timestamps = false;       // tablica nema created_at/updated_at
    protected $fillable = ['naziv', 'max_dob', 'id_savez'];

    protected $casts = [
        'max_dob' => 'integer',
    ];

    public function lige(): HasMany
    {
        return $this->hasMany(Liga::class, 'id_kategorija');
    }

    public function selekcije(): HasMany
    {
        return $this->hasMany(Selekcija::class, 'id_kategorija');
    }

    // Samo kategorije zadanog saveza.
    public function scopeZaSavez(Builder $query, int $idSavez): Builder
    {
        return $query->where('id_savez', $idSavez);
    }

    // Od najmlađe prema najstarijoj; seniori (max_dob = NULL) uvijek zadnji.
    public function scopePoDobi(Builder $query): Builder
    {
        return $query->orderByRaw('max_dob IS NULL')->orderBy('max_dob');
    }

    // Koriste li kategoriju lige ili selekcije (tada se ne smije obrisati).
    public function uUpotrebi(): bool
    {
        return $this->lige()->exists() || $this->selekcije()->exists();
    }
}
