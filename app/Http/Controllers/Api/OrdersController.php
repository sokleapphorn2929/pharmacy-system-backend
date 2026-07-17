<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $orders = Orders::all();

        // return response()->json([
        //     "message" => "Orders retrieved successfully",
        //     "data" => $orders
        // ]);

        $orders = Orders::where('user_id', auth()->id())
                        ->with('order_items.products') 
                        ->get();

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
        $request->validate([
            'order_date' => 'required|date',
            'order_status' => 'required',
            'items' => 'required|array', // Expecting an array of items from frontend
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                // 1. Create the Order
                $order = Orders::create([
                    'user_id' => auth()->id(),
                    'order_date' => $request->order_date,
                    'order_status' => $request->order_status,
                ]);

                // 2. Loop and create items using the new order's _id
                foreach ($request->items as $item) {
                    OrderItems::create([
                        'order_id'   => $order->_id, // Linking here
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'price'      => $item['price'],
                        'discount'   => $item['discount'] ?? 0,
                    ]);
                }
                return $order;
            });

            return response()->json(["message" => "Order placed successfully", "data" => $order], 201);
        } catch (\Exception $e) {
            return response()->json(["message" => "Error placing order", "error" => $e->getMessage()], 500);
        }
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
