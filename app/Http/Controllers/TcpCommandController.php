<?php

namespace App\Http\Controllers;

use App\Services\TcpServer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TcpCommandController extends Controller
{
    protected $tcpServer;

    public function __construct(TcpServer $tcpServer)
    {
        $this->tcpServer = $tcpServer;
    }

    public function sendUnlock($imei)
    {
       
        $response = $this->tcpServer->sendUnlockCommand($imei);

        return response()->json([
            'message' => $response,
        ]);
    }
}
