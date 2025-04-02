<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TcpServer;



class ScooterController 
{
    public function unlockScooter($imei)
    {
        // Prepare unlock command with R0 for unlocking
        $key = 20;  // Example key, adjust as necessary
        $userId = 1234;  // Example user ID
        $timestamp = time();  // Current Unix timestamp
        
        // The unlock command format
        $command = "*SCOS,OM,{$imei},R1,0,{$key},{$userId},{$timestamp}#\n";
        
        // Send the command to the server (TCP server connection)
        $this->sendCommandToServer($command);
    }

    public function sendCommandToServer($command)
    {
        // Open connection to the TCP server
        $host = "0.0.0.0";
        $port = 5000;

        $socket = stream_socket_client("tcp://$host:$port", $errno, $errstr);

        if (!$socket) {
            echo "❌ فشل الاتصال بالخادم: $errstr ($errno)\n";
            return;
        }

        // Send the command to the scooter
        fwrite($socket, $command);
        echo "🚀 تم إرسال الأمر: $command\n";

        // Read the response from the scooter
        $response = fread($socket, 1024);
        echo "📩 الرد من السكوتر: $response\n";

        fclose($socket);
    }
}

