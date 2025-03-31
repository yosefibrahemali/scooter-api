<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TcpServer
{
    protected $host = '0.0.0.0'; // استماع لجميع الاتصالات
    protected $port = 3000; // يمكنك تغيير البورت حسب الحاجة

    public function start()
    {
        set_time_limit(0);

        // إنشاء socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($socket, $this->host, $this->port);
        socket_listen($socket);

        Log::info("TCP Server started on {$this->host}:{$this->port}");

        while (true) {
            $client = socket_accept($socket);
            $input = socket_read($client, 1024); // استقبال البيانات من السكوتر
            Log::info("Received data: " . trim($input));

            // إرسال رد للسكوتر إذا كان يحتاج إلى تأكيد
            $response = "ACK"; // يمكنك تغييرها بناءً على متطلبات البروتوكول
            socket_write($client, $response, strlen($response));

            socket_close($client);
        }

        socket_close($socket);
    }
}
