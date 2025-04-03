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
        $request->validate([
            'scooter_id' => 'required|string',
            'user_id' => 'required|string'
        ]);

        $scooterId = $request->input('scooter_id');
        $userId = $request->input('user_id');
        $timestamp = time();
        
        $command = "*SCOS,OM,{$scooterId},L0,55,{$userId},{$timestamp}#\n";
        
        if (!$this->tcpServer->isScooterConnected($scooterId)) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter is not currently connected',
                'command' => $command
            ], 408);
        }

        $attempts = 0;
        $maxAttempts = 3;
        
        while ($attempts < $maxAttempts) {
            if ($this->tcpServer->sendCommandToScooter($scooterId, $command)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Command sent successfully',
                    'command' => $command
                ]);
            }
            
            $attempts++;
            if ($attempts < $maxAttempts) {
                sleep(1); // انتظر ثانية قبل المحاولة التالية
            }
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to send command after 3 attempts',
            'command' => $command
        ], 408);
    }
}