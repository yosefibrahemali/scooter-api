<?php

namespace App\Http\Livewire;

use App\Models\ScooterConnection;
use Livewire\Component;

class ActiveScooters extends Component
{

    public $scooters ;
    public function render()
    {
        $this->scooters = ScooterConnection::whereNull('disconnected_at')->get();
        return view('livewire.active-scooters', compact('scooters'));
    }
}
