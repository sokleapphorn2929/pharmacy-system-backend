<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payments;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payments::all();

        return response()->json([
            "message" => "Payments retrieved successfully",
            "data" => $payments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'nullable|exists:orders,_id',
            'user_id' => 'nullable|exists:users,_id',
            'total_price' => 'required|numeric|min:1',
            'total_discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qr,other',
            'payment_status' => 'required|in:unpaid,paid,refunded',
        ]);

        $validatedData['user_id'] = auth()->id();

        $payments = new Payments();
        $payments -> fill($validatedData);
        $payments -> save();

        return response()->json([
            "message" => "Payment insert successfully",
            "data" => $payments
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payments = Payments::find($id);

        if(!$payments){
            return response()->json([
                "message" => "Payment not found"
            ], 404);
        }

        return response()->json([
            "message" => "Payment retrieved successfully",
            "data" => $payments
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payments = Payments::find($id);

        if(!$payments){
            return response()->json([
                "message" => "Payment not found",
            ], 404);
        }

        $validatedData = $request->validate([
            'order_id' => 'nullable|exists:orders,_id',
            'user_id' => 'nullable|exists:users,_id',
            'total_price' => 'sometimes|numeric|min:1',
            'total_discount' => 'sometimes|numeric|min:0',
            'tax' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|in:cash,qr,other',
            'payment_status' => 'sometimes|in:unpaid,paid,refunded',
        ]);

        $payments->fill($validatedData);
        $payments->save();

        return response()->json([
            "message" => "Payment updated successfully",
            "data" => $payments
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payments = Payments::find($id);

        if(!$payments){
            return response()->json([
                "message" => "Payment not found"
            ], 404);
        }

        Invoices::where('payment_id', $id)->update(['payment_id' => null]);

        $payments->delete();

        return response()->json([
            "message" => "Payment deleted successfully"
        ]);
    }
}
