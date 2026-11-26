<?php

namespace App\Http\Controllers;

use App\Models\Igrac;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayerController extends Controller
{
    //GET /players
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = Igrac::with('klub')
            ->whereHas('klub', fn ($q) => $q->where('id_savez', $this->federationId()));

        if ($request->filled('club_id')) {
            $query->where('id_klub', $request->query('club_id'));
        }

        if ($request->filled('search')) {
            $s = '%' . $request->query('search') . '%';
            $query->where(fn ($q) => $q
                ->where('ime', 'like', $s)
                ->orWhere('prezime', 'like', $s)
                ->orWhere('broj_licence', 'like', $s));
        }

        $page = $query->orderBy('prezime')->orderBy('ime')->paginate($perPage);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($i) => $this->format($i))->values(),
            'meta' => [
                'page'      => $page->currentPage(),
                'per_page'  => $page->perPage(),
                'total'     => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }

    //POST /players
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $igrac = Igrac::create($this->toDb($data));
        $igrac->load('klub');

        return response()->json(['data' => $this->format($igrac)], 201);
    }

    //GET /players/{player}
    public function show(string $id)
    {
        $igrac = $this->findForFederation($id);
        if (!$igrac) return $this->notFound();

        return response()->json(['data' => $this->format($igrac)]);
    }

    //PUT /players/{player}
    public function update(Request $request, string $id)
    {
        $igrac = $this->findForFederation($id);
        if (!$igrac) return $this->notFound();

        $data = $this->validated($request, $igrac->id);

        $igrac->update($this->toDb($data));
        $igrac->load('klub');

        return response()->json(['data' => $this->format($igrac)]);
    }

    //DELETE /players/{player}
    public function destroy(string $id)
    {
        $igrac = $this->findForFederation($id);
        if (!$igrac) return $this->notFound();

        try {
            $igrac->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Igrač se ne može obrisati jer se još koristi.',
                    'code'    => 'resource_in_use',
                ], 409);
            }
            throw $e;
        }

        return response()->noContent();
    }

    //helper methods below

    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'date_of_birth'  => ['required', 'date', 'before_or_equal:today'],
            'license_number' => ['required', 'string', 'max:50'],
            'club_id'        => ['required', 'integer', Rule::exists('klub', 'id')->where('id_savez', $this->federationId())],
        ]);
    }

    private function toDb(array $data): array
    {
        return [
            'ime'           => $data['first_name'],
            'prezime'       => $data['last_name'],
            'datum_rodenja' => $data['date_of_birth'],
            'broj_licence'  => $data['license_number'],
            'id_klub'       => $data['club_id'],
        ];
    }

    private function format(Igrac $i): array
    {
        return [
            'id'             => $i->id,
            'first_name'     => $i->ime,
            'last_name'      => $i->prezime,
            'date_of_birth'  => $i->datum_rodenja,
            'license_number' => $i->broj_licence,
            'club'           => ['id' => $i->klub->id, 'name' => $i->klub->naziv],
        ];
    }

    private function findForFederation(string $id): ?Igrac
    {
        return Igrac::with('klub')
            ->whereHas('klub', fn ($q) => $q->where('id_savez', $this->federationId()))
            ->find($id);
    }

    private function notFound()
    {
        return response()->json([
            'message' => 'Traženi zapis ne postoji.',
            'code'    => 'not_found',
        ], 404);
    }
}