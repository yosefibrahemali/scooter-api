<?php

namespace App\Http\Controllers;

use App\Services\TcpServer;
use Illuminate\Http\Request;

class ScooterController 
{
    protected $tcpServer;

    public function __construct()
    {
        $this->tcpServer = new TcpServer();
    }

    // Route to start the TCP server and begin listening
    public function startServer()
    {
        // Start the server in the background (you can use Laravel queues or process control to manage this in production)
        $this->tcpServer->start();
        
        return response()->json(['message' => '🔵 الخادم يعمل الآن على الاستماع للاتصالات']);
    }

    // Route to send the unlock command
    public function sendUnlockCommand(Request $request)
    {
        $imei = $request->input('imei'); // Get IMEI from request

        // Send unlock command when this function is explicitly called
        $this->tcpServer->sendUnlockCommand($imei);
        
        return response()->json(['message' => '✅ تم إرسال أمر الفتح بنجاح!']);
    }
}

