<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favourites;
use Illuminate\Http\Request;
use MongoDB\Laravel\Eloquent\Casts\ObjectId;

class FavouritesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->id();

        // Enforce native MongoDB ObjectId conversion for the query match
        try {
            $mongoUserId = new ObjectId($userId);
        } catch (\Exception $e) {
            $mongoUserId = $userId; // Fallback if it's already an instance
        }

        // Query using the true BSON ObjectId instance
        $favourites = Favourites::where('user_id', $mongoUserId)
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
            'product_id' => 'required|exists:products,_id',
        ]);

        $favourites = new Favourites();
        $favourites->fill($validatedData);
        
        // Explicitly force database documents to record as real ObjectIds
        try {
            $favourites->user_id = new ObjectId(auth()->id());
            $favourites->product_id = new ObjectId($request->product_id);
        } catch (\Exception $e) {
            $favourites->user_id = auth()->id();
            $favourites->product_id = $request->product_id;
        }
        
        $favourites->save();

        return response()->json([
            "message" => "Favourite insert successfully",
            "data" => $favourites->load('products')
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
