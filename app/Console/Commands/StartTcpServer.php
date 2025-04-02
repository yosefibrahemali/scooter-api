<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServer;

class StartTcpServer extends Command
{
    // Command signature
    protected $signature = 'tcp:start';

    // Command description
    protected $description = 'Start the TCP server for scooter communication';

    // The TcpServer service
    protected $tcpServer;

    // Constructor to inject TcpServer
    public function __construct(TcpServer $tcpServer)
    {
        parent::__construct();
        $this->tcpServer = $tcpServer;
    }

    // The handle() method that gets called when the command is run
    public function handle()
    {
        $this->info('Starting the TCP server...');
        $this->tcpServer->start();  // Start the server by calling the start method
    }
}
