<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServerService;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server {port?}';
    protected $description = 'Start TCP server for scooter communication';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $port = $this->argument('port') ?? 5000;
        
        $this->info("Starting TCP server on port {$port}...");
        
        $server = new TcpServerService();
        $server->start($port);
    }
}