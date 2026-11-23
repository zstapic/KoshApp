<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Kategorija;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorInstance;

// Dobne kategorije saveza (KOSH-48).
// Svi upiti su ograničeni na savez admina, pa admin nikad ne vidi ni ne mijenja
// kategorije drugog saveza. Dok ne postoji prijava, savez je fiksno 1.
class CategoryController extends Controller
{
    // GET /categories
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $kategorije = Kategorija::zaSavez(1) // TODO: zamijeniti s $request->user()->id_savez kad bude prijava
            ->poDobi()
            ->paginate($perPage);

        return response()->json([
            'data' => CategoryResource::collection($kategorije->items()),
            'meta' => [
                'page'      => $kategorije->currentPage(),
                'per_page'  => $kategorije->perPage(),
                'total'     => $kategorije->total(),
                'last_page' => $kategorije->lastPage(),
            ],
        ]);
    }

    // POST /categories
    public function store(Request $request): JsonResponse
    {
        $idSavez = 1; // TODO: zamijeniti s $request->user()->id_savez kad bude prijava
        $data = $this->validiraj($request, $idSavez);

        $kategorija = Kategorija::create([
            'naziv'    => $data['name'],
            'max_dob'  => $data['max_age'] ?? null,
            'id_savez' => $idSavez,        // savez se nikad ne prima iz zahtjeva
        ]);

        return (new CategoryResource($kategorija))->response()->setStatusCode(201);
    }

    // GET /categories/{category}
    public function show(Request $request, string $id): JsonResponse|CategoryResource
    {
        $kategorija = $this->nadji($request, $id);
        if (!$kategorija) {
            return $this->nijePronadeno();
        }

        return new CategoryResource($kategorija);
    }

    // PUT /categories/{category}
    public function update(Request $request, string $id): JsonResponse|CategoryResource
    {
        // Prvo 404, tek onda validacija – tuđa/nepostojeća kategorija ne smije dati 422.
        $kategorija = $this->nadji($request, $id);
        if (!$kategorija) {
            return $this->nijePronadeno();
        }

        $data = $this->validiraj($request, $kategorija->id_savez, $kategorija->id);

        // Izmjena max_dob je dopuštena i kad se kategorija koristi (odluka za KOSH-48,
        // preispitati kad klijent da daljnje specifikacije).
        $kategorija->update([
            'naziv'   => $data['name'],
            'max_dob' => $data['max_age'] ?? null,
        ]);

        return new CategoryResource($kategorija);
    }

    // DELETE /categories/{category}
    public function destroy(Request $request, string $id): JsonResponse|Response
    {
        $kategorija = $this->nadji($request, $id);
        if (!$kategorija) {
            return $this->nijePronadeno();
        }

        // Eksplicitna provjera umjesto hvatanja greške stranog ključa:
        // jasnija poruka i isto ponašanje na MySQL-u i SQLiteu.
        if ($kategorija->uUpotrebi()) {
            return response()->json([
                'message' => 'Kategorija se koristi u ligama ili selekcijama i ne može se obrisati.',
                'code'    => 'resource_in_use',
            ], 409);
        }

        $kategorija->delete();

        return response()->noContent();  // 204
    }

    // Kategorija s tim id-em, ali samo ako pripada trenutnom savezu.
    private function nadji(Request $request, string $id): ?Kategorija
    {
        return Kategorija::zaSavez(1)->find($id); // TODO: zamijeniti s $request->user()->id_savez kad bude prijava
    }

    private function nijePronadeno(): JsonResponse
    {
        return response()->json([
            'message' => 'Traženi zapis ne postoji.',
            'code'    => 'not_found',
        ], 404);
    }

    /**
     * Validacija za POST i PUT (CategoryInput iz OpenAPI-ja).
     * $ignorirajId = id kategorije koja se uređuje, da se ne sudari sama sa sobom.
     */
    private function validiraj(Request $request, int $idSavez, ?int $ignorirajId = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('kategorija', 'naziv')->where('id_savez', $idSavez)->ignore($ignorirajId),
            ],
            'max_age' => [
                'nullable', 'integer', 'min:1', 'max:99',
                // max_dob je jedinstven unutar saveza – kategorije time imaju jednoznačan
                // redoslijed po dobi (potrebno za pravilo "igrač smije igrati u svojoj i starijim").
                Rule::unique('kategorija', 'max_dob')->where('id_savez', $idSavez)->ignore($ignorirajId),
            ],
        ], [
            'name.required'    => 'Naziv kategorije je obavezan.',
            'name.string'      => 'Naziv kategorije mora biti tekst.',
            'name.max'         => 'Naziv kategorije može imati najviše 50 znakova.',
            'name.unique'      => 'Kategorija s tim nazivom već postoji.',
            'max_age.integer'  => 'Najveća dob mora biti cijeli broj.',
            'max_age.min'      => 'Najveća dob mora biti barem 1.',
            'max_age.max'      => 'Najveća dob može biti najviše 99.',
            'max_age.unique'   => 'Već postoji kategorija za tu dob.',
        ]);

        // "unique" preskače NULL, pa posebno provjeravamo da postoji samo jedna
        // kategorija bez gornje granice (seniori).
        $validator->after(function (ValidatorInstance $v) use ($request, $idSavez, $ignorirajId) {
            if ($request->input('max_age') !== null || $v->errors()->has('max_age')) {
                return;
            }

            $vecPostoji = Kategorija::zaSavez($idSavez)
                ->whereNull('max_dob')
                ->when($ignorirajId, fn ($q) => $q->where('id', '!=', $ignorirajId))
                ->exists();

            if ($vecPostoji) {
                $v->errors()->add('max_age', 'Već postoji kategorija bez gornje granice dobi (seniori).');
            }
        });

        return $validator->validate();
    }
}
