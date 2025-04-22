<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DriverOrderController extends Controller
{
    public function nearbyOrders(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'List of all orders',
            'data' => [
                'orders' => Order::select("id")->get()
            ]
        ]);
    }

}
