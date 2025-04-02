<?php

namespace App\Services;

class TcpServer
{
    protected $host = "0.0.0.0";
    protected $port = 5000;
    protected $connections = []; // حفظ الاتصالات النشطة

    public function start()
    {
        $socket = stream_socket_server("tcp://$this->host:$this->port", $errno, $errstr);

        if (!$socket) {
            die("❌ Failed to start the server: $errstr ($errno)\n");
        }

        echo "🔵 TCP Server running on {$this->host}:{$this->port}...\n";

        while (true) {
            $conn = @stream_socket_accept($socket, 10);

            if ($conn) {
                stream_set_blocking($conn, false);
                $clientData = fread($conn, 1024);
                $clientData = trim($clientData);

                if (!empty($clientData)) {
                    echo "📩 Received new connection: $clientData\n";

                    if (preg_match('/\*SCOR,OM,(\d+),/', $clientData, $matches)) {
                        $imei = $matches[1];
                        $this->connections[$imei] = $conn; // حفظ الاتصال
                        echo "🔗 Connection stored for IMEI: $imei\n";
                    }
                }
            }
            usleep(500000); // تأخير بسيط لتقليل استهلاك المعالج
        }
    }

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
}








