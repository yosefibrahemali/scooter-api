<?php

namespace App\Services;

class ScooterServerService
{
    protected $host = '0.0.0.0'; // يستمع على جميع الاتصالات الخارجية
    protected $port = 3000;

    public function startServer()
    {
        // إنشاء Socket Server
        $socket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);

        if (!$socket) {
            die("خطأ في فتح السيرفر: $errstr ($errno)\n");
        }

        echo "🚀 السيرفر يعمل على {$this->host}:{$this->port}...\n";

        while ($conn = stream_socket_accept($socket)) {
            $request = fread($conn, 1024);
            echo "📥 استقبلنا طلب من السكوتر: $request\n";

            // الرد على السكوتر (مثلاً تأكيد استلام الأمر)
            fwrite($conn, "OK\n");

            fclose($conn);
        }

        fclose($socket);
    }
}