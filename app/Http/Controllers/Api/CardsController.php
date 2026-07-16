<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cards;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cards = Cards::all();

        return response()->json([
            "message" => "Card retrieved successfully",
            "data" => $cards
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
            'qty' => 'required|numeric|min:1',
        ]);

        $userId = auth()->id();
        $productId = $validatedData['product_id'];
        $incomingQty = $validatedData['qty'];

        $cards = Cards::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->first();

        if ($cards) {
            $cards->qty += $incomingQty;
            $cards->save();

            return response()->json([
                "message" => "Card quantity updated successfully",
                "data" => $cards
            ]);
        }

        $cards = new Cards();
        $validatedData['user_id'] = $userId; // Ensure correct user mapping
        $cards->fill($validatedData);
        $cards->save();

        return response()->json([
            "message" => "Card insert successfully",
            "data" => $cards
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cards = Cards::find($id);

        if(!$cards){
            return response()->json([
                "message" => "Card not found"
            ], 404);
        }

        return response()->json([
            "message" => "Card retrieved successfully",
            "data" => $cards
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cards = Cards::find($id);

        if(!$cards){
            return response()->json([
                "message" => "Card not found",
            ], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'nullable|exists:users,_id',
            'product_id' => 'nullable|exists:products,_id',
            'qty' => 'sometimes|numeric|min:1',
        ]);

        $cards->fill($validatedData);
        $cards->save();

        return response()->json([
            "message" => "Card updated successfully",
            "data" => $cards
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cards = Cards::find($id);

        if(!$cards){
            return response()->json([
                "message" => "Card not found"
            ], 404);
        }

        $cards->delete();

        return response()->json([
            "message" => "Card deleted successfully"
        ]);
    }
}
