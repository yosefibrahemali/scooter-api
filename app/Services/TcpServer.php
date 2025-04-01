<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TcpServer
{
    protected $host = "0.0.0.0"; // الاستماع على جميع عناوين IP
    protected $port = 5000;      // رقم المنفذ

    public function start()
    {
        // إنشاء Socket
        $socket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);

        if (!$socket) {
            Log::error("فشل إنشاء خادم TCP: $errstr ($errno)");
            die("فشل إنشاء الخادم: $errstr ($errno)\n");
        }

        echo "🔵 خادم TCP يعمل على {$this->host}:{$this->port}...\n";

        while ($conn = stream_socket_accept($socket)) {
            $clientData = fread($conn, 1024); // قراءة بيانات العميل
            echo "📩 استقبلنا اتصال جديد: " . trim($clientData) . "\n";
            fwrite($conn, "✅ تم استقبال رسالتك!\n"); // الرد على العميل
            fclose($conn);
        }

        fclose($socket);
    }
}
