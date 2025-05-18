<?php

namespace App\Http\Controllers;

use App\Enum\RoleCode;
use App\Models\User;
use Illuminate\Http\Request;

class SocketController extends Controller
{
    public function driverLocation(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $driver = User::find($request->driver_id);
        if (!$driver->roles()->where('role_id', RoleCode::driver)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Driverfound',
            'data' => [
                'driver_name' => $driver->name,
            ]
        ]);
    }
}
