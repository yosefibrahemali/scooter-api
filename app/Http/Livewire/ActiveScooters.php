<?php

namespace App\Http\Livewire;

use App\Models\ScooterConnection;
use Livewire\Component;

class ActiveScooters extends Component
{
    public function render()
    {
        $scooters = ScooterConnection::whereNull('disconnected_at')->get();
        return view('livewire.active-scooters', compact('scooters'));    }
}
