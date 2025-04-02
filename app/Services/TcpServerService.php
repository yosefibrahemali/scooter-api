<?php

namespace App\Services;

use React\Socket\ConnectionInterface;
use React\Socket\TcpServer;
use React\EventLoop\Factory;

class TcpServerService
{
    protected $loop;
    protected $server;
    protected $connections = [];

    public function __construct()
    {
        $this->loop = Factory::create();
    }

    public function start($port = 5000)
    {
        $this->server = new TcpServer("0.0.0.0:$port", $this->loop);

        $this->server->on('connection', function (ConnectionInterface $connection) {
            $remoteAddress = $connection->getRemoteAddress();
            $this->connections[$remoteAddress] = $connection;
            
            echo "New connection from {$remoteAddress}\n";

            $connection->on('data', function ($data) use ($connection, $remoteAddress) {
                echo "Received from {$remoteAddress}: {$data}";
                
                // معالجة البيانات الواردة من السكوتر
                $this->handleScooterData($data, $connection);
            });

            $connection->on('close', function () use ($remoteAddress) {
                echo "Connection {$remoteAddress} closed\n";
                unset($this->connections[$remoteAddress]);
            });
        });

        echo "Server running on port {$port}\n";
        $this->loop->run();
    }



   
    
    
    protected function handleScooterData($data, ConnectionInterface $connection)
    {
        if (preg_match('/\*SCOR,OM,(\d+),/', $data, $matches)) {
            $scooterId = $matches[1];
            $this->connections[$scooterId] = $connection;
            
            // ... باقي معالجة البيانات ...
        }
    }

    public function sendCommandToScooter($scooterId, $command)
    {
        if (isset($this->connections[$scooterId])) {
            $this->connections[$scooterId]->write($command);
            echo "Command sent to scooter {$scooterId}: {$command}";
            return true;
        }
        
        echo "Scooter {$scooterId} not connected\n";
        return false;
    }




}