<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TcpServer;


class ScooterController 
{
    public function unlockScooter($imei)
    {
        $tcpServer = new TcpServer();
        $command = $tcpServer->unlockCommand($imei);  // Send unlock command
        return response()->json([
            'message' => '✅ تم إرسال أمر الفتح بنجاح!',
            'command' => $command,
            'response' => 'Your response from the scooter here' // Add actual response here if needed
        ]);
    }

    public function lockScooter($imei)
    {
        $tcpServer = new TcpServer();
        $command = $tcpServer->lockCommand($imei);  // Send lock command
        return response()->json([
            'message' => '✅ تم إرسال أمر القفل بنجاح!',
            'command' => $command,
            'response' => 'Your response from the scooter here' // Add actual response here if needed
        ]);
    }
}    
