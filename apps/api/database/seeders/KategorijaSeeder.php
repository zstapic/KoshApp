<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Početne dobne kategorije saveza (KOSH-48).
//
// Popis sadrži samo kategorije koje savez trenutno koristi. Nova kategorija se može dodati:
//  1) kroz web aplikaciju (admin saveza) – uobičajeni put, ne treba deploy;
//  2) dodavanjem retka u POCETNE i pokretanjem
//       php artisan db:seed --class=KategorijaSeeder
//     Seeder je idempotentan: dodaje samo ono što savezu nedostaje, postojeće ne dira.
class KategorijaSeeder extends Seeder
{
    /** naziv => max_dob (null = bez gornje granice, seniori) */
    public const POCETNE = [
        'U12'     => 12,
        'U14'     => 14,
        'U16'     => 16,
        'Seniori' => null,
    ];

    // Samostalno pokretanje: dopuni kategorije za svaki postojeći savez.
    public function run(): void
    {
        foreach (DB::table('savez')->pluck('id') as $idSavez) {
            self::zaSavez($idSavez);
        }
    }

    /**
     * Dodaje savezu kategorije iz POCETNE koje još nema i vraća [naziv => id] za sve njih.
     * Kategorija se preskače ako savez već ima isti naziv ILI istu max_dob
     * (max_dob je jedinstven unutar saveza).
     */
    public static function zaSavez(int $idSavez): array
    {
        $ids = [];

        foreach (self::POCETNE as $naziv => $maxDob) {
            $postojeca = DB::table('kategorija')
                ->where('id_savez', $idSavez)
                ->where(fn ($q) => $q->where('naziv', $naziv)
                    ->orWhere(fn ($q) => $maxDob === null
                        ? $q->whereNull('max_dob')
                        : $q->where('max_dob', $maxDob)))
                ->value('id');

            $ids[$naziv] = $postojeca ?? DB::table('kategorija')->insertGetId([
                'naziv'    => $naziv,
                'max_dob'  => $maxDob,
                'id_savez' => $idSavez,
            ]);
        }

        return $ids;
    }
}
