<?php

namespace App\Http\Controllers;

use App\Models\ScooterConnection;
use Illuminate\Http\Request;
use App\Services\TcpService;


class TestController
{
    public function index()
    {
        $scooters = ScooterConnection::whereNull('disconnected_at')->get();
        return view('test', compact('scooters'));
    }
    public function sendCommand()
    {
        $imei = "868351077123154";
        $commandType = "R0";
        $value = 0;

        $response = TcpService::sendCommand($imei, $commandType, $value);
        
        return response()->json(['message' => $response]);
    }
}
