<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScooterController 
{
    protected $host = "127.0.0.1"; // أو عنوان IP السيرفر
    protected $port = 5000; // منفذ TCP للسكوتر

    public function unlockScooter($imei)
    {
        $timestamp = time() * 1000; // الوقت بالميلي ثانية
        $command = "*SCOS,OM,{$imei},R0,0,20,1234,{$timestamp}#\n";

        // فتح اتصال TCP
        $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 5);

        if (!$socket) {
            return response()->json(['error' => "❌ فشل الاتصال بالخادم: $errstr ($errno)"], 500);
        }

        // إرسال الأمر
        fwrite($socket, $command);
        Log::info("🚀 تم إرسال الأمر: " . $command);

        // استقبال الرد من السكوتر
        $response = fread($socket, 1024);
        fclose($socket);

        Log::info("📩 الرد من السكوتر: " . trim($response));

        return response()->json([
            'message' => '✅ تم إرسال أمر الفتح بنجاح!',
            'command' => $command,
            'response' => trim($response)
        ]);
    }
}
