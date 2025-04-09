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
use Barryvdh\DomPDF\Facade\Pdf;



use Milon\Barcode\Facades\DNS1DFacade;




use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\SMBPrintConnector;

Route::get('/print/{code}', function ($code) {
    try {
        // 🔧 عدّل بيانات المستخدم والكمبيوتر واسم الطابعة حسب حالتك
        $username = 'YourWindowsUsername';
        $password = 'YourWindowsPassword';
        $ip = '192.168.1.10'; // IP جهاز ويندوز الذي موصّل عليه الطابعة
        $printerShare = 'Xprinter'; // اسم المشاركة (Share name)

        $connector = new SMBPrintConnector("smb://$username:$password@$ip/$printerShare");
        $printer = new Printer($connector);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("***** باركود المنتج *****\n");
        $printer->text("-------------------------\n");
        $printer->text("الكود: $code\n");
        $printer->feed(4);
        $printer->cut();
        $printer->close();

        return response()->json(['status' => 'تمت الطباعة']);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});









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
