<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lat = $request->coordinates->latitude;
        $lng = $request->coordinates->longitude;
        
        $stores = Store::query()
            ->orderBy('name')
            ->get();

        $response = [
            'success' => true,
            'message' => 'List of all stores',
            'stores' => $stores
        ];

        return response()->json($response);
    }
}
