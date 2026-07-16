<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favourites;
use Illuminate\Http\Request;

class FavouritesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $favourites = Favourites::with('product')->get();

        // return response()->json([
        //     "message" => "Favourite retrieved successfully",
        //     "data" => $favourites
        // ]);

        $userId = auth()->id();

        // Query using a simple where clause, Laravel MongoDB will handle the internal casting
        $favourites = Favourites::where('user_id', $userId)
            ->with('products')
            ->get();

        return response()->json([
            "message" => "Favourite retrieved successfully",
            "data" => $favourites
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'nullable|exists:users,_id',
            'product_id' => 'nullable|exists:products,_id',
        ]);

        $validatedData['user_id'] = auth()->id();

        $favourites = new Favourites();
        $favourites -> fill($validatedData);
        $favourites -> save();

        return response()->json([
            "message" => "Favourite insert successfully",
            "data" => $favourites
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $favourites = Favourites::find($id);

        if(!$favourites){
            return response()->json([
                "message" => "Favourite not found"
            ], 404);
        }

        return response()->json([
            "message" => "Favourite retrieved successfully",
            "data" => $favourites
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $favourites = Favourites::find($id);

        if(!$favourites){
            return response()->json([
                "message" => "Favourite not found",
            ], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'nullable|exists:users,_id',
            'product_id' => 'nullable|exists:products,_id',
        ]);

        $favourites->fill($validatedData);
        $favourites->save();

        return response()->json([
            "message" => "Favourite updated successfully",
            "data" => $favourites
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $favourites = Favourites::find($id);

        if(!$favourites){
            return response()->json([
                "message" => "Favourite not found"
            ], 404);
        }

        $favourites->delete();

        return response()->json([
            "message" => "Favourite deleted successfully"
        ]);
    }
}
