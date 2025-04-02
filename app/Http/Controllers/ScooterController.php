<?php

namespace App\Http\Controllers;

use App\Services\TcpServer;
use Illuminate\Http\Request;

class ScooterController 
{
    protected $tcpServer;

    public function __construct(TcpServer $tcpServer)
    {
        $this->tcpServer = $tcpServer;
    }

    public function sendCommand($imei)
    {
        $response = $this->tcpServer->sendUnlockCommand($imei);

        return response()->json([
            'message' => $response,
        ]);
    }
}

