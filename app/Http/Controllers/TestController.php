<?php

namespace App\Http\Controllers;

use App\Models\ScooterConnection;
use Illuminate\Http\Request;
use App\Services\TcpService;


class TcpClient
{

    protected $host = "127.0.0.1"; // عنوان الخادم
    protected $port = 5000;       // نفس المنفذ الذي يستمع عليه السكوتر

    public function sendUnlockCommand($imei)
    {
        $timestamp = time() * 1000; // تحويل الوقت إلى ميلي ثانية
        $command = "*SCOS,OM,{$imei},R0,0,20,1234,{$timestamp}#\n";

        $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 5);

        if (!$socket) {
            die("❌ فشل الاتصال بالخادم: $errstr ($errno)\n");
        }

        fwrite($socket, $command); // إرسال الأمر
        echo "🚀 تم إرسال الأمر: $command\n";

        $response = fread($socket, 1024); // استقبال الرد
        echo "📩 الرد من السكوتر: $response\n";

        fclose($socket);
    }
}
