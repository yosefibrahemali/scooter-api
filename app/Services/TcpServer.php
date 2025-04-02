<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class TcpServer
{
    protected $host = "0.0.0.0";
    protected $port = 5000;

    public function start()
    {
        $socket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);
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
                    echo "📩 Received data: $clientData\n";

                    if (preg_match('/\*SCOR,OM,(\d+),/', $clientData, $matches)) {
                        $imei = $matches[1];
                        Redis::set("scooter:$imei", serialize($conn));  // تخزين الاتصال في Redis
                        echo "🔗 Connection stored for IMEI: $imei\n";
                    }
                }
            }

            usleep(500000);
        }

        fclose($socket);
    }

    public function sendUnlockCommand($imei)
    {
        $conn = unserialize(Redis::get("scooter:$imei"));

        if (!$conn) {
            return "⚠️ No active connection found for IMEI: $imei";
        }

        $command = "*SCOS,OM,{$imei},L0,55,1234," . time() . "#\n";
        fwrite($conn, $command);
        echo "🚀 Sent unlock command to IMEI: $imei\n";

        return "✅ Unlock command sent to IMEI: $imei";
    }
}
