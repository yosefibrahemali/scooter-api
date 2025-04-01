<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TcpServer
{
    protected $host = "0.0.0.0"; // الاستماع على جميع عناوين IP
    protected $port = 5000;      // رقم المنفذ

    public function start()
    {
        $socket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);

        if (!$socket) {
            Log::error("فشل إنشاء خادم TCP: $errstr ($errno)");
            die("فشل إنشاء الخادم: $errstr ($errno)\n");
        }

        echo "🔵 خادم TCP يعمل على {$this->host}:{$this->port}...\n";

        stream_set_timeout($socket, 5); // إضافة مهلة لتجنب التعليق

        while (true) {
            $conn = @stream_socket_accept($socket, 10); // انتظار 10 ثوانٍ قبل المهلة
            
            if ($conn) {
                $clientData = fread($conn, 1024); 
                echo "📩 استقبلنا اتصال جديد: " . trim($clientData) . "\n";
                fwrite($conn, "✅ تم استقبال رسالتك!\n"); 
                fclose($conn);
            } else {
                echo "⏳ لم يتم استقبال أي اتصال، الاستمرار في الاستماع...\n";
            }
        }

        fclose($socket);
    }

}
