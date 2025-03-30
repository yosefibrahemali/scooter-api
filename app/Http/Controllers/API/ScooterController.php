<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TcpServer;

class ScooterController extends Controller
{
   
    public function startScooter()
    {
        $host = '138.199.198.151';
        $port = '3000';
        $timeout = 3; // تقليل وقت الانتظار

        $context = stream_context_create([
            'socket' => ['connect_timeout' => 5]
        ]);

        $socket = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);

        if (!$socket) {
            return response()->json([
                'success' => false,
                'message' => "فشل الاتصال بالسكوتر: $errstr ($errno)"
            ], 500);
        }

        stream_set_timeout($socket, 3);

        // إرسال أمر فتح القفل للسكوتر
        $command = "*SCOS,OM,868351077123154,S6#\r\n"; // تأكد من إضافة \r\n
        fwrite($socket, $command);

        // قراءة الاستجابة من السكوتر
        $response = fread($socket, 1024);

        // تحويل الاستجابة إلى Hexadecimal لعرضها بشكل صحيح
        $responseHex = bin2hex($response);
        echo "📩 Response (Hex): $responseHex\n"; // عرض الاستجابة بالتنسيق Hex

        fclose($socket);

        return response()->json([
            'success' => true,
            'message' => "تم إرسال أمر تشغيل السكوتر",
            'response' => trim($responseHex) // عرض الاستجابة بالتنسيق Hex في الرد
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

