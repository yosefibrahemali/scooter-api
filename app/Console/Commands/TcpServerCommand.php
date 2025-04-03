<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServerService;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server {port?}';

    public function handle()
    {
        $port = $this->argument('port') ?? 5000;
        $server = TcpServerService::getInstance();
        
        $this->info("Starting TCP server on port {$port}...");
        $this->info("Keep this process running in background");
        $this->info("Use supervisor to manage the process in production");
        
        $server->start($port);
    }
}