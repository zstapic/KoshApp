<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $savez = DB::table('savez')->insertGetId(['naziv' => 'KS Varaždinske županije']);

        // demo users, one per role (password: "password", only for local/dev)
        DB::table('korisnik')->insert([
            ['id_savez' => null,   'korisnicko_ime' => 'superadmin', 'lozinka_hash' => Hash::make('password'), 'uloga' => 'SuperAdmin',  'aktivan' => true],
            ['id_savez' => $savez, 'korisnicko_ime' => 'admin',      'lozinka_hash' => Hash::make('password'), 'uloga' => 'AdminSaveza', 'aktivan' => true],
        ]);
        $zapisnicar = DB::table('korisnik')->insertGetId(
            ['id_savez' => $savez, 'korisnicko_ime' => 'zapisnicar', 'lozinka_hash' => Hash::make('password'), 'uloga' => 'Zapisnicar', 'aktivan' => true]
        );

        // categories (only U14 gets a league in the demo)
        DB::table('kategorija')->insert(['naziv' => 'U12', 'max_dob' => 12, 'id_savez' => $savez]);
        $u14 = DB::table('kategorija')->insertGetId(['naziv' => 'U14', 'max_dob' => 14, 'id_savez' => $savez]);
        DB::table('kategorija')->insert(['naziv' => 'U16', 'max_dob' => 16, 'id_savez' => $savez]);
        DB::table('kategorija')->insert(['naziv' => 'Seniori', 'max_dob' => null, 'id_savez' => $savez]);

        $sezona = DB::table('sezona')->insertGetId([
            'naziv' => '2026/27', 'godina_pocetka' => 2026, 'godina_zavrsetka' => 2027, 'id_savez' => $savez,
        ]);

        $liga = DB::table('liga')->insertGetId(['naziv' => 'Županijska liga U14', 'id_kategorija' => $u14, 'id_savez' => $savez]);
        $izdanje = DB::table('izdanje_lige')->insertGetId(['id_liga' => $liga, 'id_sezona' => $sezona]);

        // clubA
        $klubA = DB::table('klub')->insertGetId(['naziv' => 'KK Varaždin', 'id_savez' => $savez]);
        $selA  = DB::table('selekcija')->insertGetId(['naziv' => 'KK Varaždin U14', 'id_klub' => $klubA, 'id_kategorija' => $u14]);
        DB::table('ekipa_u_ligi')->insert(['id_izdanje_lige' => $izdanje, 'id_selekcija' => $selA]);

        // players clubA: insert into igrac, then link each one to the selection
        $igraciA = [
            ['Ivan', 'Horvat', '2013-03-14'],
            ['Luka', 'Novak', '2013-05-02'],
            ['Marko', 'Jurić', '2013-01-27'],
            ['Filip', 'Babić', '2013-07-19'],
            ['Josip', 'Vuković', '2013-09-08'],
            ['Matej', 'Knežević', '2013-11-30'],
            ['Petar', 'Pavlović', '2013-02-11'],
            ['Karlo', 'Božić', '2013-06-23'],
            ['Dominik', 'Blažević', '2013-10-05'],
            ['Leon', 'Grgić', '2013-04-17'],
        ];
        foreach ($igraciA as $i => [$ime, $prezime, $datum]) {
            $igrac = DB::table('igrac')->insertGetId([
                'ime' => $ime, 'prezime' => $prezime, 'datum_rodenja' => $datum,
                'broj_licence' => 'VZ-A-' . ($i + 1), 'id_klub' => $klubA,
            ]);
            DB::table('nastupa_za_selekciju')->insert([
                'id_igrac' => $igrac, 'id_selekcija' => $selA, 'id_sezona' => $sezona,
            ]);
        }

        // clubB
        $klubB = DB::table('klub')->insertGetId(['naziv' => 'KK Trnovec', 'id_savez' => $savez]);
        $selB  = DB::table('selekcija')->insertGetId(['naziv' => 'KK Trnovec U14', 'id_klub' => $klubB, 'id_kategorija' => $u14]);
        DB::table('ekipa_u_ligi')->insert(['id_izdanje_lige' => $izdanje, 'id_selekcija' => $selB]);

        // players clubB: insert into igrac, then link each one to the selection
        $igraciB = [
            ['Pero', 'Kovačić', '2013-08-22'],
            ['Tomislav', 'Marić', '2013-03-03'],
            ['Ante', 'Šarić', '2013-12-12'],
            ['Mihael', 'Perić', '2013-05-28'],
            ['Borna', 'Tomić', '2013-01-09'],
            ['Lovro', 'Kolar', '2013-07-01'],
            ['Jakov', 'Matić', '2013-09-16'],
            ['Noa', 'Lovrić', '2013-02-25'],
            ['Roko', 'Mlinarić', '2013-10-21'],
            ['Fran', 'Vidović', '2013-06-07'],
        ];
        foreach ($igraciB as $i => [$ime, $prezime, $datum]) {
            $igrac = DB::table('igrac')->insertGetId([
                'ime' => $ime, 'prezime' => $prezime, 'datum_rodenja' => $datum,
                'broj_licence' => 'VZ-B-' . ($i + 1), 'id_klub' => $klubB,
            ]);
            DB::table('nastupa_za_selekciju')->insert([
                'id_igrac' => $igrac, 'id_selekcija' => $selB, 'id_sezona' => $sezona,
            ]);
        }

        // coaches (one head and one assistant per club)
        DB::table('trener')->insert([
            ['ime' => 'Dario', 'prezime' => 'Horvat', 'broj_licence' => 'VZ-T-1', 'id_klub' => $klubA],
            ['ime' => 'Nikola', 'prezime' => 'Bešić', 'broj_licence' => 'VZ-T-2', 'id_klub' => $klubA],
            ['ime' => 'Zoran', 'prezime' => 'Petrović', 'broj_licence' => 'VZ-T-3', 'id_klub' => $klubB],
            ['ime' => 'Igor', 'prezime' => 'Radić', 'broj_licence' => 'VZ-T-4', 'id_klub' => $klubB],
        ]);

        // hall and referees
        $dvorana = DB::table('dvorana')->insertGetId(['naziv' => 'Sportska dvorana Ludbreg', 'mjesto' => 'Ludbreg', 'id_savez' => $savez]);

        $sudac1 = DB::table('sudac')->insertGetId(['ime' => 'Brko', 'prezime' => 'Brkati', 'id_savez' => $savez]);
        $sudac2 = DB::table('sudac')->insertGetId(['ime' => 'Božo', 'prezime' => 'Borić', 'id_savez' => $savez]);
        $sudac3 = DB::table('sudac')->insertGetId(['ime' => 'Stjepan', 'prezime' => 'Hrastić', 'id_savez' => $savez]);

        // one scheduled match (no events yet)
        DB::table('utakmica')->insert([
            'broj_utakmice' => 1, 'kolo' => 1, 'id_izdanje_lige' => $izdanje,
            'id_selekcija_domacin' => $selA, 'id_selekcija_gost' => $selB,
            'datum_vrijeme' => '2026-11-15 10:00:00', 'id_dvorana' => $dvorana,
            'id_glavni_sudac' => $sudac1, 'id_sudac_1' => $sudac2, 'id_sudac_2' => $sudac3,
            'id_zapisnicar' => $zapisnicar,
        ]);

        // 3 tables are left with no demo data:
        // - utakmica_sastav
        // - utakmica_trener
        // - aktivnost
        // because they hold data that is inserted before or during a match
    }
}
