<?php

namespace App\Services;

class TcpServer
{
    protected $host = "0.0.0.0"; // استماع على جميع العناوين
    protected $port = 5000; // المنفذ
    protected $connections = []; // تخزين الاتصالات بناءً على IMEI

    public function start()
    {
        // إنشاء السيرفر والاستماع للاتصالات
        $socket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);

        if (!$socket) {
            die("❌ Failed to start the server: $errstr ($errno)\n");
        }

        echo "🔵 TCP Server running on {$this->host}:{$this->port}...\n";

        while (true) {
            // قبول الاتصال الجديد دون إغلاق الاتصالات السابقة
            $conn = @stream_socket_accept($socket, 10);

            if ($conn) {
                stream_set_blocking($conn, false); // جعل الاتصال غير متزامن

                // قراءة البيانات القادمة من السكوتر
                $clientData = fread($conn, 1024);
                $clientData = trim($clientData);

                if (!empty($clientData)) {
                    echo "📩 Received: $clientData\n";

                    // استخراج IMEI من البيانات المستلمة
                    if (preg_match('/\*SCOR,OM,(\d+),/', $clientData, $matches)) {
                        $imei = $matches[1];
                        $this->connections[$imei] = $conn; // تخزين الاتصال حسب IMEI
                        echo "🔗 Connection stored for IMEI: $imei\n";
                    } else {
                        echo "⚠️ IMEI not found in message: $clientData\n";
                    }
                }

                // إبقاء الاتصال مفتوحًا دون إنهاء الاتصال
                $this->keepAlive($conn);
            }

            usleep(500000); // تأخير صغير لمنع استهلاك المعالج
        }

        fclose($socket);
    }

    // إرسال أوامر إلى السكوتر
    public function sendUnlockCommand($imei)
    {
        if (!isset($this->connections[$imei])) {
            return "⚠️ No active connection found for IMEI: $imei";
        }

        $conn = $this->connections[$imei];

        $key = 55;
        $userId = 1234;
        $timestamp = time();

        $command = "*SCOS,OM,{$imei},L0,{$key},{$userId},{$timestamp}#\n";

        fwrite($conn, $command);
        echo "🚀 Sent unlock command to IMEI {$imei}: $command\n";

        return "✅ Unlock command sent to IMEI: $imei";
    }

    // إبقاء الاتصال مفتوحًا
    private function keepAlive($conn)
    {
        stream_set_timeout($conn, 0, 500000); // تعيين مهلة الاتصال
        fwrite($conn, "PING\n"); // إرسال "PING" للتأكد من استمرار الاتصال
    }
}