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
        $scooterId = $request->input('scooter_id');
        $userId = $request->input('user_id');
        $timestamp = time();
        
        $command = "*SCOS,OM,{$scooterId},L0,55,{$userId},{$timestamp}#\n";
        
        // محاولة الإرسال 3 مرات مع تأخير بينها
        $attempts = 0;
        while ($attempts < 3) {
            if ($this->tcpServer->sendCommandToScooter($scooterId, $command)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Command sent successfully',
                    'command' => $command
                ]);
            }
            sleep(1);
            $attempts++;
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Scooter not connected after 3 attempts',
            'command' => $command
        ], 408);
    }
    
}