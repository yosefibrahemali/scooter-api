<?php

namespace App\Http\Controllers;

use App\Services\TcpServerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ScooterController extends Controller
{
    public function unlock(Request $request)
    {
        $validated = $request->validate([
            'scooter_id' => 'required|string|size:15',
            'user_id' => 'required|string|min:4'
        ]);

        $server = TcpServerService::getInstance();
        if (!$server) {
            return response()->json([
                'success' => false,
                'message' => 'TCP server not running'
            ], 503);
        }

        $scooterId = $validated['scooter_id'];
        $command = $this->buildCommand($scooterId, $validated['user_id']);

        if (!$server->sendCommandToScooter($scooterId, $command)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send command to scooter',
                'connected_scooters' => $server->getConnectedScooters(),
                'command' => $command
            ], 408);
        }

        return response()->json([
            'success' => true,
            'message' => 'Command sent successfully',
            'command' => $command
        ]);
    }

    protected function buildCommand($scooterId, $userId)
    {
        $timestamp = time();
        return "*SCOS,OM,{$scooterId},L0,55,{$userId},{$timestamp}#\n";
    }
}