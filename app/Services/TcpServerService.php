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
    protected $scooterConnections = []; // جديد: لتخزين الاتصانات حسب معرف السكوتر


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
            
            // حفظ الاتصال بكلتا الطريقتين
            $remoteAddress = $connection->getRemoteAddress();
            $this->connections[$remoteAddress] = $connection;
            $this->scooterConnections[$scooterId] = $connection;
            
            echo "Scooter {$scooterId} connected from {$remoteAddress}\n";
            
            // معالجة أنواع الأوامر المختلفة
            $this->processScooterCommand($scooterId, $data);
        }
    }

    protected function processScooterCommand($scooterId, $data)
    {
        $parts = explode(',', $data);
        $commandType = $parts[3] ?? '';
        
        switch ($commandType) {
            case 'Q0':
                echo "Scooter {$scooterId} sent status update\n";
                break;
            case 'D0':
                echo "Scooter {$scooterId} sent location data\n";
                break;
            case 'H0':
                echo "Scooter {$scooterId} sent health data\n";
                break;
        }
    }

    public function sendCommandToScooter($scooterId, $command)
    {
        if (!isset($this->scooterConnections[$scooterId])) {
            echo "Scooter {$scooterId} not found in active connections\n";
            return false;
        }

        try {
            $this->scooterConnections[$scooterId]->write($command);
            echo "Command successfully sent to scooter {$scooterId}: {$command}";
            return true;
        } catch (\Exception $e) {
            echo "Failed to send command to scooter {$scooterId}: " . $e->getMessage() . "\n";
            unset($this->scooterConnections[$scooterId]);
            return false;
        }
    }

    public function isScooterConnected($scooterId)
    {
        return isset($this->scooterConnections[$scooterId]);
    }



    public function checkConnections()
    {
        foreach ($this->scooterConnections as $scooterId => $connection) {
            try {
                // يمكنك إرسال ping أو التحقق من حالة الاتصال
                $connection->write("*PING#\n");
            } catch (\Exception $e) {
                unset($this->scooterConnections[$scooterId]);
                echo "Cleaned up disconnected scooter: {$scooterId}\n";
            }
        }
    }

    


}
    // Starting TCP server on port 5000...
    // Server running on port 5000
    // New connection from tcp://41.254.83.113:35353
    // Received from tcp://41.254.83.113:35353: *SCOR,OM,868351077123154,Q0,370,98,17#
    // Received from tcp://41.254.83.113:35353: *SCOR,OM,868351077123154,H0,1,370,18,98,0#
    // Received from tcp://41.254.83.113:35353: *SCOR,OM,868351077123154,D0,1,222533,A,3222.0894,N,01506.2620,E,13,0.9,020425,15,M,A#
    // Received from tcp://41.254.83.113:35353: *SCOR,OM,868351077123154,H0,1,370,18,98,0#
    // Received from tcp://41.254.83.113:35353: *SCOR,OM,868351077123154,D0,1,223034,A,3222.0901,N,01506.2625,E,13,0.9,020425,14,M,A#
    // Received from tcp://41.254.83.113:35353: *SCOR,OM,868351077123154,H0,1,370,17,98,0#
