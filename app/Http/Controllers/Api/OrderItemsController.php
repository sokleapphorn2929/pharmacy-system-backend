<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItems;
use Illuminate\Http\Request;

class OrderItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $order_items = OrderItems::all();

        return response()->json([
            "message" => "Order Items retrieved successfully",
            "data" => $order_items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'nullable|exists:orders,_id',
            'product_id' => 'nullable|exists:products,_id',
            'qty' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
            'discount' => 'required|numeric|min:0',
        ]);

        $order_items = new OrderItems();
        $order_items -> fill($validatedData);
        $order_items -> save();

        return response()->json([
            "message" => "Order Items insert successfully",
            "data" => $order_items
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order_items = OrderItems::find($id);

        if(!$order_items){
            return response()->json([
                "message" => "Order Items not found"
            ], 404);
        }

        return response()->json([
            "message" => "Order Items retrieved successfully",
            "data" => $order_items
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order_items = OrderItems::find($id);

        if(!$order_items){
            return response()->json([
                "message" => "Order Items not found",
            ], 404);
        }

        $validatedData = $request->validate([
            'order_id' => 'nullable|exists:orders,_id',
            'product_id' => 'nullable|exists:products,_id',
            'qty' => 'sometimes|numeric|min:1',
            'price' => 'sometimes|numeric|min:1',
            'discount' => 'sometimes|numeric|min:0',
        ]);

        $order_items->fill($validatedData);
        $order_items->save();

        return response()->json([
            "message" => "Order Items updated successfully",
            "data" => $order_items
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order_items = OrderItems::find($id);

        if(!$order_items){
            return response()->json([
                "message" => "Order Items not found"
            ], 404);
        }

        $order_items->delete();

        return response()->json([
            "message" => "Order Items deleted successfully"
        ]);
    }
}
