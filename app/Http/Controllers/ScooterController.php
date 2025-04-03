<?php

namespace App\Http\Controllers;

use App\Services\TcpServerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ScooterController extends Controller
{
    protected $tcpServer;

    public function __construct(TcpServerService $tcpServer)
    {
        $this->tcpServer = $tcpServer;
    }

    public function unlock(Request $request)
    {
        $validated = $request->validate([
            'scooter_id' => 'required|string|size:15',
            'user_id' => 'required|string|min:4'
        ]);

        $scooterId = $validated['scooter_id'];
        $userId = $validated['user_id'];
        $timestamp = time();
        $command = "*SCOS,OM,{$scooterId},L0,55,{$userId},{$timestamp}#\n";

        // التحقق من اتصال السكوتر أولاً
        $connectedScooters = $this->tcpServer->getConnectedScooters();
        if (!in_array($scooterId, $connectedScooters)) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not connected. Currently connected: ' . implode(', ', $connectedScooters),
                'command' => $command
            ], 408);
        }

        // محاولة الإرسال
        if ($this->tcpServer->sendCommandToScooter($scooterId, $command)) {
            return response()->json([
                'success' => true,
                'message' => 'Command sent successfully',
                'command' => $command
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send command',
            'command' => $command
        ], 500);
    }

    public function listConnected()
    {
        return response()->json([
            'connected_scooters' => $this->tcpServer->getConnectedScooters()
        ]);
    }
}