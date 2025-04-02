<?php

namespace App\Services;

class TcpServer
{
    protected $host = "0.0.0.0";  // Listen on all IP addresses
    protected $port = 5000;       // Port to bind to
    protected $connections = [];  // Store active connections by IMEI

    public function start()
    {
        $socket = stream_socket_server("tcp://$this->host:$this->port", $errno, $errstr);

        if (!$socket) {
            die("❌ Failed to start the server: $errstr ($errno)\n");
        }

        echo "🔵 TCP Server running on {$this->host}:{$this->port}...\n";

        while (true) {
            $conn = @stream_socket_accept($socket, 10); // Accept connection

            if ($conn) {
                $clientData = fread($conn, 1024);
                $clientData = trim($clientData);
                echo "📩 Received new connection: " . $clientData . "\n";

                if (strpos($clientData, "*SCOR") !== false) {
                    echo "✅ Scooter connected\n";
                    
                    // Extract IMEI number dynamically
                    preg_match('/\*SCOR,OM,(\d+),/', $clientData, $matches);
                    if (isset($matches[1])) {
                        $imei = $matches[1];
                        $this->connections[$imei] = $conn; // Store connection
                        echo "🔗 Connection stored for IMEI: $imei\n";
                    } else {
                        echo "⚠️ IMEI not found in the message!\n";
                    }
                }
            } else {
                echo "⏳ No connection received, waiting...\n";
            }
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





