<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TcpServer;

class StartTcpServer extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'تشغيل خادم TCP للاستماع إلى الاتصالات الواردة';

    public function handle()
    {
        $server = new TcpServer();
        $server->start();
    }
}
