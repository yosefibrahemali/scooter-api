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
        // تحليل البيانات الواردة من السكوتر
        if (strpos($data, '*SCOR,OM') !== false) {
            // هذا رد من السكوتر على أمر
            $parts = explode(',', $data);
            $status = $parts[3] ?? null;
            $userId = $parts[2] ?? null;
            $timestamp = $parts[6] ?? null;
            
            // معالجة الرد هنا
            echo "Received response from scooter - Status: {$status}, UserID: {$userId}\n";
        }
        
        // يمكنك إضافة المزيد من معالجات البيانات هنا
    }

    public function sendCommandToScooter($scooterId, $command)
    {
        foreach ($this->connections as $address => $connection) {
            // هنا يمكنك التحقق من أن الاتصال هو للسكوتر المطلوب
            // (قد تحتاج إلى تتبع معرفات السكوتر مع عناوينهم)
            
            $connection->write($command);
            echo "Sent command to scooter {$scooterId}: {$command}\n";
            return true;
        }
        
        echo "Scooter {$scooterId} not connected\n";
        return false;
    }
}