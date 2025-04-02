<?php

use App\Http\Controllers\ScooterController;
use App\Http\Controllers\TcpClient;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Artisan;
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


Route::get('/start-server', function () {
    Artisan::call('tcp:server');
    return "🚀 TCP Server Started!";
});

// Route to send unlock command to scooter
Route::post('/send-command', [ScooterController::class, 'sendCommand']);