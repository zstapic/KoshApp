<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Models\Klub;

class ClubController extends Controller
{
    //GET /clubs
    public function index()
    {
        $klubovi = Klub::all();
        
        return response()->json([
            'data' => $klubovi->map(function ($klub) {
                return [
                    'id' => $klub->id,
                    'name' => $klub->naziv
                ];
            }),
            'meta' => ['page' => 1, 'per_page' => 25, 'total' => count($klubovi), 'last_page' => 1],
        ]);
    }

    //POST /clubs
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $klub = Klub::create([
            'naziv'    => $data['name'],
            'id_savez' => 1, // TODO: zamijeniti s $request->user()->id_savez kad bude prijava
        ]);

        return response()->json([
            'data' => [
                'id' => $klub->id, 
                'name' => $klub->naziv
            ],
        ], 201);
    }

    //GET /clubs/{club}
    public function show(string $id)
    {
        $klub = Klub::find($id);

        if (!$klub) {
            return response()->json([
                'message' => 'Traženi zapis ne postoji.',
                'code' => 'not_found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $klub->id,
                'name' => $klub->naziv
            ],
        ], 200);
    }

    //PUT /clubs/{club}
    public function update(Request $request, string $id)
    {
        $klub = Klub::find($id);

        if (!$klub) {
            return response()->json([
                'message' => 'Traženi zapis ne postoji.',
                'code' => 'not_found',
            ], 404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $klub->update(['naziv' => $data['name']]);

        return response()->json([
            'data' => [
                'id'   => $klub->id,
                'name' => $klub->naziv,
            ],
        ], 200);
    }

    //DELETE /clubs/{club}
    public function destroy(string $id)
    {
        $klub = Klub::find($id);

        if (!$klub) {
            return response()->json([
                'message' => 'Traženi zapis ne postoji.',
                'code' => 'not_found',
            ], 404);
        }

        try {
            $klub->delete();
        } catch (QueryException $e) {
            // 23000 = povreda stranog ključa (klub ima igrače, selekcije...)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Klub se ne može obrisati jer se još koristi.',
                    'code'    => 'resource_in_use',
                ], 409);
            }
            throw $e;
        }

        return response()->noContent();  // 204
    }
}
