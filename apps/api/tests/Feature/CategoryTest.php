<?php

namespace Tests\Feature;

use Database\Seeders\KategorijaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// KOSH-48 – dobne kategorije.
// Dok ne postoji prijava, CategoryController koristi fiksni savez s id = 1.
// TODO login: dodati actingAs($admin) i testove 401 / 403.
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private int $savez;
    private int $drugiSavez;

    protected function setUp(): void
    {
        parent::setUp();

        // id = 1 mora odgovarati fiksnom savezu u CategoryController
        $this->savez      = DB::table('savez')->insertGetId(['id' => 1, 'naziv' => 'KS Varaždinske županije']);
        $this->drugiSavez = DB::table('savez')->insertGetId(['id' => 2, 'naziv' => 'KS Zagreb']);
    }

    private function kategorija(string $naziv, ?int $maxDob, ?int $savez = null): int
    {
        return DB::table('kategorija')->insertGetId([
            'naziv' => $naziv, 'max_dob' => $maxDob, 'id_savez' => $savez ?? $this->savez,
        ]);
    }

    // ---------------------------------------------------------------- POST

    public function test_kreira_kategoriju(): void
    {
        $this->postJson('/api/categories', ['name' => 'U14', 'max_age' => 14])
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'max_age']])
            ->assertJsonPath('data.name', 'U14')
            ->assertJsonPath('data.max_age', 14);

        $this->assertDatabaseHas('kategorija', ['naziv' => 'U14', 'max_dob' => 14, 'id_savez' => $this->savez]);
    }

    public function test_kreira_seniore_bez_gornje_granice(): void
    {
        $this->postJson('/api/categories', ['name' => 'Seniori', 'max_age' => null])
            ->assertCreated()
            ->assertJsonPath('data.max_age', null);
    }

    public function test_savez_se_ne_moze_poslati_u_zahtjevu(): void
    {
        $this->postJson('/api/categories', ['name' => 'U14', 'max_age' => 14, 'id_savez' => $this->drugiSavez])
            ->assertCreated();

        $this->assertDatabaseHas('kategorija', ['naziv' => 'U14', 'id_savez' => $this->savez]);
    }

    public function test_validacija_obaveznih_i_granicnih_vrijednosti(): void
    {
        $this->postJson('/api/categories', [])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson('/api/categories', ['name' => str_repeat('a', 51)])->assertJsonValidationErrors('name');
        $this->postJson('/api/categories', ['name' => 'X', 'max_age' => 0])->assertJsonValidationErrors('max_age');
        $this->postJson('/api/categories', ['name' => 'X', 'max_age' => 100])->assertJsonValidationErrors('max_age');
        $this->postJson('/api/categories', ['name' => 'X', 'max_age' => 'abc'])->assertJsonValidationErrors('max_age');
    }

    public function test_naziv_je_jedinstven_unutar_saveza(): void
    {
        $this->kategorija('U14', 14);

        $this->postJson('/api/categories', ['name' => 'U14', 'max_age' => 15])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name' => 'Kategorija s tim nazivom već postoji.']);
    }

    public function test_max_age_je_jedinstven_unutar_saveza(): void
    {
        $this->kategorija('U14', 14);

        $this->postJson('/api/categories', ['name' => 'Kadeti', 'max_age' => 14])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['max_age' => 'Već postoji kategorija za tu dob.']);
    }

    public function test_smije_postojati_samo_jedna_kategorija_bez_gornje_granice(): void
    {
        $this->kategorija('Seniori', null);

        $this->postJson('/api/categories', ['name' => 'Veterani', 'max_age' => null])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_age');

        // izostavljen max_age znači isto što i null
        $this->postJson('/api/categories', ['name' => 'Veterani'])
            ->assertJsonValidationErrors('max_age');
    }

    public function test_isti_naziv_i_dob_dopusteni_u_drugom_savezu(): void
    {
        $this->kategorija('U14', 14, $this->drugiSavez);
        $this->kategorija('Seniori', null, $this->drugiSavez);

        $this->postJson('/api/categories', ['name' => 'U14', 'max_age' => 14])->assertCreated();
        $this->postJson('/api/categories', ['name' => 'Seniori', 'max_age' => null])->assertCreated();
    }

    // ---------------------------------------------------------------- GET

    public function test_popis_sortiran_po_dobi_seniori_zadnji_samo_vlastiti_savez(): void
    {
        $this->kategorija('Seniori', null);
        $this->kategorija('U16', 16);
        $this->kategorija('U12', 12);
        $this->kategorija('Tuđa U10', 10, $this->drugiSavez);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.*.name', ['U12', 'U16', 'Seniori'])
            ->assertJsonPath('meta', ['page' => 1, 'per_page' => 25, 'total' => 3, 'last_page' => 1]);
    }

    public function test_paginacija(): void
    {
        foreach ([11, 12, 13] as $dob) {
            $this->kategorija("U$dob", $dob);
        }

        $this->getJson('/api/categories?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.*.name', ['U13'])
            ->assertJsonPath('meta', ['page' => 2, 'per_page' => 2, 'total' => 3, 'last_page' => 2]);
    }

    public function test_dohvat_jedne_kategorije(): void
    {
        $id = $this->kategorija('U14', 14);

        $this->getJson("/api/categories/$id")
            ->assertOk()
            ->assertExactJson(['data' => ['id' => $id, 'name' => 'U14', 'max_age' => 14]]);
    }

    public function test_kategorija_drugog_saveza_vraca_404(): void
    {
        $tuda = $this->kategorija('U14', 14, $this->drugiSavez);

        $this->getJson("/api/categories/$tuda")->assertNotFound()->assertJsonPath('code', 'not_found');
        $this->putJson("/api/categories/$tuda", ['name' => 'X', 'max_age' => 9])->assertNotFound();
        $this->deleteJson("/api/categories/$tuda")->assertNotFound();
        $this->getJson('/api/categories/9999')->assertNotFound();

        $this->assertDatabaseHas('kategorija', ['id' => $tuda, 'naziv' => 'U14']);
    }

    // ---------------------------------------------------------------- PUT

    public function test_izmjena_kategorije(): void
    {
        $id = $this->kategorija('U14', 14);

        $this->putJson("/api/categories/$id", ['name' => 'U15', 'max_age' => 15])
            ->assertOk()
            ->assertJsonPath('data.name', 'U15')
            ->assertJsonPath('data.max_age', 15);
    }

    public function test_izmjena_bez_promjene_ne_pada_na_jedinstvenosti(): void
    {
        $id      = $this->kategorija('Seniori', null);
        $u14     = $this->kategorija('U14', 14);

        $this->putJson("/api/categories/$id", ['name' => 'Seniori', 'max_age' => null])->assertOk();
        $this->putJson("/api/categories/$u14", ['name' => 'U14', 'max_age' => 14])->assertOk();
    }

    public function test_izmjena_ne_smije_preuzeti_dob_druge_kategorije(): void
    {
        $this->kategorija('U12', 12);
        $u14 = $this->kategorija('U14', 14);

        $this->putJson("/api/categories/$u14", ['name' => 'U14', 'max_age' => 12])
            ->assertJsonValidationErrors('max_age');
    }

    public function test_izmjena_dobi_dopustena_i_kad_se_kategorija_koristi(): void
    {
        $id = $this->kategorija('U14', 14);
        DB::table('liga')->insert(['naziv' => 'Liga U14', 'id_kategorija' => $id, 'id_savez' => $this->savez]);

        $this->putJson("/api/categories/$id", ['name' => 'U14', 'max_age' => 13])->assertOk();
    }

    // ---------------------------------------------------------------- DELETE

    public function test_brisanje_nekoristene_kategorije(): void
    {
        $id = $this->kategorija('U14', 14);

        $this->deleteJson("/api/categories/$id")->assertNoContent();
        $this->assertDatabaseMissing('kategorija', ['id' => $id]);
    }

    public function test_brisanje_blokirano_ako_kategoriju_koristi_liga(): void
    {
        $id = $this->kategorija('U14', 14);
        DB::table('liga')->insert(['naziv' => 'Liga U14', 'id_kategorija' => $id, 'id_savez' => $this->savez]);

        $this->deleteJson("/api/categories/$id")
            ->assertStatus(409)
            ->assertJsonPath('code', 'resource_in_use');
        $this->assertDatabaseHas('kategorija', ['id' => $id]);
    }

    public function test_brisanje_blokirano_ako_kategoriju_koristi_selekcija(): void
    {
        $id   = $this->kategorija('U14', 14);
        $klub = DB::table('klub')->insertGetId(['naziv' => 'KK Varaždin', 'id_savez' => $this->savez]);
        DB::table('selekcija')->insert(['naziv' => 'KK Varaždin U14', 'id_klub' => $klub, 'id_kategorija' => $id]);

        $this->deleteJson("/api/categories/$id")->assertStatus(409);
    }

    // ---------------------------------------------------------------- seeder

    public function test_seeder_je_idempotentan_i_postuje_jedinstvenost(): void
    {
        // savez već ima vlastitu kategoriju za dob 14 pod drugim nazivom
        $vlastita = $this->kategorija('Pionirke', 14);

        $prvi  = KategorijaSeeder::zaSavez($this->savez);
        $drugi = KategorijaSeeder::zaSavez($this->savez);

        $this->assertSame($prvi, $drugi);
        $this->assertSame($vlastita, $prvi['U14']);   // nije stvorena duplikat za dob 14
        $this->assertSame(4, DB::table('kategorija')->where('id_savez', $this->savez)->count());
    }
}
