<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Models\Sudac;

class RefereeController extends Controller
{
    //GET /referees
    public function index()
    {
        $suci = Sudac::all();
        
        return response()->json([
            'data' => $suci->map(function ($sudac) {
                return [
                    'id' => $sudac->id,
                    'first_name' => $sudac->ime,
                    'last_name' => $sudac->prezime,
                ];
            }),
            'meta' => ['page' => 1, 'per_page' => 25, 'total' => count($suci), 'last_page' => 1],
        ]);
    }

    //POST /referees
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100']
        ]);

        $sudac = Sudac::create([
            'ime'    => $data['first_name'],
            'prezime'    => $data['last_name'],
            'id_savez' => 1, // TODO: zamijeniti s $request->user()->id_savez kad bude prijava
        ]);

        return response()->json([
            'data' => [
                'id' => $sudac->id, 
                'first_name' => $sudac->ime,
                'last_name' => $sudac->prezime
            ],
        ], 201);
    }

    //GET /referee/{referee}
    public function show(string $id)
    {
        $sudac = Sudac::find($id);

        if (!$sudac) {
            return response()->json([
                'message' => 'Traženi zapis ne postoji.',
                'code' => 'not_found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $sudac->id,
                'first_name' => $sudac->ime,
                'last_name' => $sudac->prezime
            ],
        ], 200);
    }

    //PUT /referee/{referee}
    public function update(Request $request, string $id)
    {
        $sudac = Sudac::find($id);

        if (!$sudac) {
            return response()->json([
                'message' => 'Traženi zapis ne postoji.',
                'code' => 'not_found',
            ], 404);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100']
        ]);

        $sudac->update(['ime' => $data['first_name'], 'prezime' => $data['last_name']] );  //možda ne dela
       
        return response()->json([
            'data' => [
                'id'   => $sudac->id,
                'first_name' => $sudac->ime,
                'last_name' => $sudac->prezime,
            ],
        ], 200);
    }

    //DELETE /referee/{referee}
    public function destroy(string $id)
    {
        $sudac = Sudac::find($id);

        if (!$sudac) {
            return response()->json([
                'message' => 'Traženi zapis ne postoji.',
                'code' => 'not_found',
            ], 404);
        }

        try {
            $sudac->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Sudac se ne može obrisati jer se još koristi.',
                    'code'    => 'resource_in_use',
                ], 409);
            }
            throw $e;
        }

        return response()->noContent();  // 204
    }
}
