<?php

namespace App\Http\Controllers;

use App\Models\ScooterConnection;
use Illuminate\Http\Request;

class TestController
{
    public function index()
    {
        $scooters = ScooterConnection::whereNull('disconnected_at')->get();
        return view('test', compact('scooters'));
    }
}
