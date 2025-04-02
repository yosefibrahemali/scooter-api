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
            $conn = @stream_socket_accept($socket, 60); // زيادة مهلة الانتظار
        
            if ($conn) {
                stream_set_blocking($conn, false);
                $clientData = stream_get_contents($conn);
                $clientData = trim($clientData);
        
                if (!empty($clientData)) {
                    echo "📩 Received data: $clientData\n";
        
                    if (preg_match('/\*SCOR,OM,(\d+),/', $clientData, $matches)) {
                        $imei = $matches[1];
        
                        $this->connections[$imei] = [
                            'conn' => $conn,
                            'last_active' => time()
                        ];
        
                        echo "🔗 Connection stored for IMEI: $imei\n";
                    } else {
                        echo "⚠️ IMEI not found in message: $clientData\n";
                    }
                } else {
                    echo "⚠️ Received empty data from client\n";
                }
            }
        
            // تنظيف الاتصالات القديمة
            foreach ($this->connections as $imei => $info) {
                if (time() - $info['last_active'] > 300) { // 300 ثانية (5 دقائق)
                    unset($this->connections[$imei]);
                    echo "❌ Connection for IMEI {$imei} removed due to inactivity\n";
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

        $conn = $this->connections[$imei]['conn'];

        if (!$conn) {
            return "⚠️ Connection resource is not valid!";
        }

        $key = 55;
        $userId = 1234;
        $timestamp = time();

        $command = "*SCOS,OM,{$imei},L0,{$key},{$userId},{$timestamp}#\n";

        fwrite($conn, $command);
        echo "🚀 Sent unlock command to IMEI {$imei}: $command\n";

        return "✅ Unlock command sent to IMEI: $imei";
    }

}
