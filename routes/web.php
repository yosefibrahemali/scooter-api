<?php

use App\Http\Controllers\ScooterController;
use App\Http\Controllers\TcpClient;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/connect-scooter/{imei}', [ScooterController::class, 'connectToScooter']);

// Route to send the unlock command
Route::get('/unlock-scooter/{imei}', [ScooterController::class, 'sendUnlockCommand']);

