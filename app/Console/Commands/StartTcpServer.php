<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServer;

class StartTcpServer extends Command
{
    protected $signature = 'tcp:start';
    protected $description = 'Start the TCP server';

    public function handle()
    {
        $tcpServer = new TcpServer();
        $tcpServer->start();
    }
}