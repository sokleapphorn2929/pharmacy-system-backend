<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoices;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoices::all();

        return response()->json([
            "message" => "Invoice retrieved successfully",
            "data" => $invoices
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'payment_id' => 'nullable|exists:payments,_id',
            'admin_id' => 'nullable|exists:admins,_id',
            'invoice_number' => 'required|string|max:255|unique:invoices',
            'invoice_create_at' => 'required|date',
        ]);

        $validatedData['admin_id'] = auth()->id();

        $invoices = new Invoices();
        $invoices -> fill($validatedData);
        $invoices -> save();

        return response()->json([
            "message" => "Invoice insert successfully",
            "data" => $invoices
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoices = Invoices::find($id);

        if(!$invoices){
            return response()->json([
                "message" => "Invoice not found"
            ], 404);
        }

        return response()->json([
            "message" => "Invoice retrieved successfully",
            "data" => $invoices
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoices = Invoices::find($id);

        if(!$invoices){
            return response()->json([
                "message" => "Invoice not found",
            ], 404);
        }

        $validatedData = $request->validate([
            'payment_id' => 'nullable|exists:payments,_id',
            'admin_id' => 'nullable|exists:admins,_id',
            'invoice_number' => 'sometimes|string|max:255|unique:invoices',
            'invoice_create_at' => 'sometimes|date',
        ]);

        $invoices->fill($validatedData);
        $invoices->save();

        return response()->json([
            "message" => "Invoice updated successfully",
            "data" => $invoices
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $invoices = Invoices::find($id);

        if(!$invoices){
            return response()->json([
                "message" => "Invoice not found"
            ], 404);
        }

        $invoices->delete();

        return response()->json([
            "message" => "Invoice deleted successfully"
        ]);
    }
}
