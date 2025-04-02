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
        
        // بناء الأمر وفقاً للبروتوكول المطلوب
        $command = "*SCOS,OM,{$scooterId},L0,55,{$userId},{$timestamp}#\n";
        
        $result = $this->tcpServer->sendCommandToScooter($scooterId, $command);
        
        return response()->json([
            'success' => $result,
            'command' => $command
        ]);
    }
}