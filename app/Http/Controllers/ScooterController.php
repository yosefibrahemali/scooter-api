<?php

namespace App\Http\Controllers;

use App\Services\TcpServerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class ScooterController extends Controller
{
    public function unlock(Request $request)
    {
        $server = TcpServerService::getInstance();
        
        if (!$server->isRunning()) {
            return response()->json([
                'success' => false,
                'message' => 'TCP server not running',
                'solution' => 'Run the server first with: php artisan tcp:server 5000'
            ], 503);
        }

        $scooterId = $request->input('scooter_id');
        $connectedScooters = $server->getConnectedScooters();

        if (!in_array($scooterId, $connectedScooters)) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not connected',
                'connected_scooters' => $connectedScooters,
                'solution' => 'Ensure scooter is powered on and connected to network'
            ], 404);
        }

        $command = $this->buildCommand($scooterId, $request->input('user_id'));
        
        if ($server->sendCommandToScooter($scooterId, $command)) {
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

    // ... (بقية الدوال)
}
