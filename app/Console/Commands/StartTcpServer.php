<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServer;

class StartTcpServer extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'Start the TCP server to listen for scooter connections';

    public function handle()
    {
        $server = new TcpServer();
        $server->start();
    }
}
