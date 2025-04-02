<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use App\Services\TcpServer;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'Start the TCP server';

    public function handle()
    {
        $server = IoServer::factory(new TcpServer(), 5000);
        echo "🔵 TCP Server running on 0.0.0.0:5000...\n";
        $server->run();
    }
}
