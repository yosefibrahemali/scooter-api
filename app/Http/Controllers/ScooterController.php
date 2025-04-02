<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TcpServer;
use Illuminate\Routing\Controller;

class ScooterController extends Controller
{
    protected $tcpServer;

    public function __construct()
    {
        $this->tcpServer = new TcpServer();
    }

    public function unlockScooter($imei)
    {
       

        if (!$imei) {
            return response()->json(['error' => 'IMEI is required'], 400);
        }

        $result = $this->tcpServer->sendUnlockCommand($imei);

        return response()->json(['message' => $result]);
    }
}