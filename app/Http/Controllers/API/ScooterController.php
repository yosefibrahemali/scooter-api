<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TcpServer;

class ScooterController extends Controller
{
    public function startScooter()
    {
        $host = env('SCOOTER_IP', '138.199.198.151'); // عنوان IP السكوتر
        $port = env('SCOOTER_PORT', 16994); // المنفذ الذي يستمع عليه السكوتر

        $socket = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$socket) {
            return response()->json([
                'success' => false,
                'message' => "خطأ في الاتصال بالسكوتر: $errstr ($errno)"
            ], 500);
        }

        // أمر تشغيل السكوتر
        $command = "*SCOS,OM,868351077123154,S6#\n";

        fwrite($socket, $command);

        // قراءة الرد من السكوتر
        $response = fgets($socket, 1024);
        fclose($socket);

        return response()->json([
            'success' => true,
            'message' => "تم إرسال أمر تشغيل السكوتر",
            'response' => trim($response)
        ]);
    }

    public function unlock(Request $request)
    {
        $serverIp = '138.199.198.151'; // ضع هنا IP سيرفرك الفعلي
        $port = 16994;

        $socket = stream_socket_client("tcp://$serverIp:$port", $errno, $errstr, 30);
        
        if (!$socket) {
            return response()->json(['error' => "❌ Error: $errstr ($errno)"]);
        } else {
            $unlockCommand = hex2bin('AABBCCDD'); // ضع هنا كود فتح القفل من البروتوكول
            fwrite($socket, $unlockCommand);
            fclose($socket);
            return response()->json(['message' => '🛴 Unlock command sent successfully!']);
        }
    }


    protected $unlockService;

    public function __construct(TcpServer $unlockService)
    {
        $this->unlockService = $unlockService;
    }

    public function unlockScooter()
    {
        // استبدل بعنوان IP ومنفذ القفل الفعلي
        $ip = "138.199.198.151"; 
        $port = 16994;

        // إرسال أمر فتح القفل
        $response = $this->unlockService->sendUnlockCommand($ip, $port);

        return response()->json(['message' => $response]);
    }



}

