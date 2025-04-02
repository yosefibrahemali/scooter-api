<?php

namespace App\Services;

class TcpServer
{
    protected $host = "138.199.198.151";
    protected $port = 5000;
    protected $connections = [];

    public function start()
    {
        $socket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);
    
        if (!$socket) {
            die("❌ Failed to start the server: $errstr ($errno)\n");
        }
    
        echo "🔵 TCP Server running on {$this->host}:{$this->port}...\n";
    
        while (true) {
            echo "⏳ Waiting for a new connection...\n"; // طباعة عند انتظار اتصال جديد
            
            $conn = @stream_socket_accept($socket, 10);
    
            if ($conn) {
                echo "✅ Connection established!\n";
                stream_set_blocking($conn, true); // تغيير من false إلى true
                sleep(2); // إبقاء الاتصال مفتوحًا مؤقتًا
                
                $clientData = fread($conn, 1024);
                $clientData = trim($clientData);
                
                if (!empty($clientData)) {
                    echo "📩 Received data: $clientData\n";
                } else {
                    echo "⚠️ Received empty data from client\n";
                }
            }
            
    
            usleep(500000);
        }
    
        fclose($socket);
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
