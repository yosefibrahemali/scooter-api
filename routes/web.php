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
Route::get('/unlock', function(){
    $client = new TcpClient();
    $client->sendUnlockCommand('868351077123154');

});

Route::get('/unlock/{imei}', [ScooterController::class, 'unlockScooter']);

// Route::get('/', [TestController::class, 'index']);
