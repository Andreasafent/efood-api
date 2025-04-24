<?php

use App\Http\Controllers\DriverOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello driver!'
    ]);
});

Route::prefix('auth')->middleware("setAuthRole:driver")->group(base_path('routes/auth.php'));


Route::middleware(['auth:sanctum', 'checkRole:driver'])->group(function(){
    Route::prefix("orders")
        ->controller(DriverOrderController::class)
        ->group(function() {
            Route::get("nearby", 'nearbyOrders');
            Route::post("take", 'takeOrder');
            Route::get("details/{id}", 'orderDetails');
        });
});
