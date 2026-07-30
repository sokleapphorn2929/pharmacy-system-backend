<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use App\Models\Categories;
use App\Models\Invoices;
use App\Models\Orders;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countsByDay = array_fill(0, 7, 0);

        // Fetch orders created within the current week (or all-time aggregation)
        // Here we grab orders and parse their created_at date using Carbon
        $orders = Orders::all();

        foreach ($orders as $order) {
            if ($order->created_at) {
                $date = Carbon::parse($order->created_at);
                // dayOfWeekIso returns 1 for Monday through 7 for Sunday
                $isoDay = $date->dayOfWeekIso; 
                
                // Map ISO day (1=Mon ... 7=Sun) to array index (0=Mon ... 6=Sun)
                $index = $isoDay - 1;
                $countsByDay[$index]++;
            }
        }

        return response()->json([
            'category' => Categories::count(),
            'brand'    => Brands::count(),
            'order'    => Orders::count(),
            'invoice'  => Invoices::count(),
            'weekly_orders' => $countsByDay,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
