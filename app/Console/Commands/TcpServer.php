<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServerService;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server {port?}';
    protected $description = 'Start TCP server for scooter communication';

    public function handle()
    {
        $port = $this->argument('port') ?? 8080;
        
        $server = new TcpServerService();
        $server->start($port);
    }
}