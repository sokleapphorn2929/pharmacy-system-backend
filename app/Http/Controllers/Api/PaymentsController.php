<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderSuccessfulMail;
use App\Models\Invoices;
use App\Models\Orders;
use App\Models\Payments;
use Illuminate\Http\Request;
use App\Notifications\OrderPaidNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            'order_id' => 'required|exists:orders,_id',
            // 'user_id' => 'nullable|exists:users,_id',
            'total_price' => 'required|numeric|min:1',
            'total_discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qr,other',
            'payment_status' => 'required|in:unpaid,paid,refunded',
        ]);

        // $validatedData['total_price'] = floatval($request->total_price) + 2;

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

        $wasPaid = $payments->payment_status === 'paid';

        $payments->fill($validatedData);
        $payments->save();

        $isNowPaid = $payments->payment_status === 'paid';

        if ($isNowPaid && !$wasPaid) {
            try {
                Orders::where('_id', $payments->order_id)->update(['order_status' => 'completed']);
                
                $existingInvoice = Invoices::where('payment_id', $payments->_id)->first();
                if (!$existingInvoice) {
                    Invoices::create([
                        'payment_id' => $payments->_id,
                        'order_id'   => $payments->order_id,
                        'user_id'    => $payments->user_id,
                        'admin_id'   => auth()->id(),
                        'invoice_number' => 'INV-' . strtoupper(uniqid()),
                        'created_at' => now(),
                    ]);
                }

                $order = Orders::find($payments->order_id);
                $userId = $payments->user_id ?? ($order ? $order->user_id : null);
                $user = $userId ? \App\Models\User::find($userId) : null;
                
                if ($user && $order) {
                    DB::connection('mongodb')->table('notifications')->insert([
                        '_id' => (string) \Illuminate\Support\Str::uuid(),
                        'type' => OrderPaidNotification::class,
                        'notifiable_type' => \App\Models\User::class,
                        'notifiable_id' => (string) $user->_id,
                        'data' => [
                            'title' => 'Order Successful - Payment Paid',
                            'message' => 'Your payment has been verified by the admin. Order #' . $order->_id . ' is successfully processed.',
                            'order_id' => (string) $order->_id,
                            'status' => 'paid',
                        ],
                        'read_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (!empty($user->email)) {
                        Mail::to($user->email)->send(new OrderSuccessfulMail([
                            '_id' => (string) $order->_id,
                            'total_price' => $payments->total_price ?? ($order->total_price ?? 0),
                        ]));
                    }
                }
            } catch (\Exception $e) {
                // This will log the EXACT error message to storage/logs/laravel.log instead of throwing a 500 crash
                Log::error("PAYMENT UPDATE ERROR: " . $e->getMessage() . " on line " . $e->getLine());
                return response()->json([
                    "message" => "Failed to process payment update",
                    "error" => $e->getMessage()
                ], 500);
            }
        }
        
        // Scenario 2: Changed from Paid -> Unpaid (or Refunded)
        elseif (!$isNowPaid && $wasPaid) {
            Orders::where('_id', $payments->order_id)->update(['order_status' => 'pending']);
            Invoices::where('payment_id', $payments->_id)->delete();
        }

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
