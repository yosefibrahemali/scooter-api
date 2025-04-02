<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServerService;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'Start TCP server for scooter communication';

    public function handle()
    {
        $port = 5000;
        
        $server = new TcpServerService();
        $server->start($port);
    }
}