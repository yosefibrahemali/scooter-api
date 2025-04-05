<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScooterController;
use App\Http\Controllers\TcpCommandController;
use App\Services\TcpServerService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// use App\Http\Controllers\ScooterController;


use Illuminate\Support\Facades\Redis;

Route::get('/unlock-scooter/{id}', function ($id) {
    Redis::publish('scooter-commands', json_encode([
        'command' => 'unlock',
        'scooterId' => $id,
        'userId' => '1234'
    ]));

    return response()->json([
        'status' => 'ok',
        'message' => "Unlock command published for scooter {$id}"
    ]);
});




Route::get('/scooter/status', function() {
    $server = TcpServerService::getInstance();
    
    return response()->json([
        'running' => $server->isRunning(),
        'connected_scooters' => $server->getConnectedScooters(),
        'total_connections' => count($server->getConnectedScooters())
    ]);
});
Route::get('/scooter/connected', [ScooterController::class, 'listConnected']);
Route::post('/scooter/unlock', [ScooterController::class, 'unlock']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);





Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
