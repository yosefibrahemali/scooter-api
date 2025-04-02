<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TcpServer;
use Illuminate\Routing\Controller;

class TcpCommandController extends Controller
{
    protected $tcpServer;

    public function __construct(TcpServer $tcpServer)
    {
        $this->tcpServer = $tcpServer;
    }

    public function sendUnlock(Request $request)
    {
        $request->validate([
            'imei' => 'required|string',
        ]);

        $imei = $request->imei;
        $response = $this->tcpServer->sendUnlockCommand($imei);

        return response()->json([
            'message' => $response,
        ]);
    }
}
