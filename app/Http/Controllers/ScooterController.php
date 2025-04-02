<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TcpServer;
use Illuminate\Routing\Controller;

class ScooterController extends Controller
{
    protected $tcpServer;

    public function __construct(TcpServer $tcpServer)
    {
        $this->tcpServer = $tcpServer;
    }

    public function unlockScooter($imei)
    {
        $response = $this->tcpServer->sendUnlockCommand($imei);
        return response()->json(['message' => $response]);
    }
}
