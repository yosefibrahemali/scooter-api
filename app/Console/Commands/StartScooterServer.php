<?php

namespace App\Console\Commands;

use App\Services\ScooterServerService;
use Illuminate\Console\Command;

class StartScooterServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scooter:server';
    


    /**
     * The console command description.
     *
     * @var string
     */

     protected $description = 'تشغيل خادم TCP لاستقبال بيانات السكوتر';
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $server = new ScooterServerService();
        $server->startServer();
    }
}
