<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Payments;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Orders::all();

        return response()->json([
            "message" => "Orders retrieved successfully",
            "data" => $orders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'order_date' => 'required|date',
            'order_status' => 'required|in:pending,completed,cancelled,rejected',
        ]);

        $validatedData['user_id'] = auth()->id();

        $orders = new Orders();
        $orders -> fill($validatedData);
        $orders -> save();

        return response()->json([
            "message" => "Orders insert successfully",
            "data" => $orders
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orders = Orders::find($id);

        if(!$orders){
            return response()->json([
                "message" => "Order not found"
            ], 404);
        }

        return response()->json([
            "message" => "Order retrieved successfully",
            "data" => $orders
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $orders = Orders::find($id);

        if(!$orders){
            return response()->json([
                "message" => "Order not found",
            ], 404);
        }

        $validatedData = $request->validate([
            'order_date' => 'sometimes|date',
            'order_status' => 'sometimes|in:pending,completed,cancelled,rejected',
        ]);

        $validatedData['user_id'] = auth()->id();

        $orders->fill($validatedData);
        $orders->save();

        return response()->json([
            "message" => "Order updated successfully",
            "data" => $orders
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $orders = Orders::find($id);

        if(!$orders){
            return response()->json([
                "message" => "Order not found"
            ], 404);
        }

        OrderItems::where('order_item_id', $id)->delete();
        Payments::where('payment_id', $id)->update(['payment_id' => null]);

        $orders->delete();

        return response()->json([
            "message" => "Order deleted successfully"
        ]);
    }
}
