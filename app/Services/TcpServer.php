<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;


class TcpServer
{
    protected $host = "0.0.0.0";  // Listen on all IP addresses
    protected $port = 5000;       // Port to bind to

    // Start the server
    public function start()
    {
        // Create the server socket
        $socket = stream_socket_server("tcp://$this->host:$this->port", $errno, $errstr);

        if (!$socket) {
            die("❌ Failed to start the server: $errstr ($errno)\n");
        }

        echo "🔵 TCP Server running on {$this->host}:{$this->port}...\n";

        while (true) {
            // Accept a new connection
            $conn = @stream_socket_accept($socket, 10); // 10-second timeout

            if ($conn) {
                $clientData = fread($conn, 1024); // Read up to 1024 bytes
                $clientData = trim($clientData);  // Remove extra spaces or line breaks
                echo "📩 Received new connection: " . $clientData . "\n";

                // Handle incoming message
                if (strpos($clientData, "*SCOR") !== false) {
                    echo "✅ Received a command from the scooter\n";
                    $imei = '868351077123154';  // Example IMEI
                    $this->sendUnlockCommand($conn, $imei);
                }

                fclose($conn);  // Close the connection
            } else {
                echo "⏳ No connection received, continuing to listen...\n";
            }
        }

        fclose($socket); // Close the server socket when done
    }

    // Send the unlock command (e.g., L0 command)
    public function sendUnlockCommand($conn, $imei)
    {
        $key = 55; // Example key value
        $userId = 1234; // Example user ID
        $timestamp = time(); // Get the current Unix timestamp

        // Construct the L0 unlock command
        $command = "*SCOS,OM,{$imei},L0,{$key},{$userId},{$timestamp}#\n";

        // Send the unlock command to the scooter
        fwrite($conn, $command);
        echo "🚀 Sent unlock command: $command\n";
    }
}







