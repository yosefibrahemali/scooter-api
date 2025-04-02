<?php

namespace App\Http\Controllers;

use App\Services\TcpServer;
use Illuminate\Http\Request;

class ScooterController 
{
    protected $tcpServer;

    public function __construct(TcpServer $tcpServer)
    {
        $this->tcpServer = $tcpServer; // Inject TcpServer
    }

    // Route to start the TCP server and begin listening
    public function startServer()
    {
        // Start the server in the background (you can use Laravel queues or process control to manage this in production)
        $this->tcpServer->start();
        
        return response()->json(['message' => '🔵 الخادم يعمل الآن على الاستماع للاتصالات']);
    }

    // Route to send the unlock command
    public function sendCommand($imei)
    {
      
        // Send the unlock command (L0) to the scooter
        $this->tcpServer->sendUnlockCommand($imei);
        
        // Return a response (could be a success message, etc.)
        return response()->json([
            'message' => 'Unlock command sent successfully.',
        ]);
    }
}

