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
    protected $scooterConnections = [];
    protected $scooterAddressMap = []; // خريطة لعناوين السكوتر

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
        if (preg_match('/\*SCOR,OM,(\d+),([A-Za-z0-9]+)/', $data, $matches)) {
            $scooterId = $matches[1];
            $commandType = $matches[2];
            $remoteAddress = $connection->getRemoteAddress();

            // تحديث بيانات الاتصال
            $this->connections[$remoteAddress] = $connection;
            $this->scooterConnections[$scooterId] = $connection;
            $this->scooterAddressMap[$scooterId] = $remoteAddress;

            echo "[CONNECTION] Scooter {$scooterId} connected from {$remoteAddress}\n";

            // معالجة الأوامر الواردة
            switch ($commandType) {
                case 'Q0':
                    echo "[STATUS] Scooter {$scooterId} status update\n";
                    break;
                case 'H0':
                    echo "[HEALTH] Scooter {$scooterId} health data\n";
                    break;
                case 'D0':
                    echo "[DATA] Scooter {$scooterId} location data\n";
                    break;
                default:
                    echo "[UNKNOWN] Scooter {$scooterId} sent unknown command: {$commandType}\n";
            }

            // إرسال تأكيد الاستلام
            $connection->write("*ACK,{$scooterId},{$commandType}#\n");
            return;
        }

        echo "[ERROR] Invalid data format from {$connection->getRemoteAddress()}\n";
        $connection->write("*ERROR,INVALID_FORMAT#\n");
    }

    public function sendCommandToScooter($scooterId, $command)
    {
        if (!isset($this->scooterConnections[$scooterId])) {
            echo "[ERROR] Scooter {$scooterId} not in active connections list\n";
            echo "[DEBUG] Active scooters: " . json_encode(array_keys($this->scooterConnections)) . "\n";
            return false;
        }

        try {
            $this->scooterConnections[$scooterId]->write($command);
            echo "[COMMAND] Successfully sent to {$scooterId}: {$command}";
            return true;
        } catch (\Exception $e) {
            echo "[ERROR] Failed to send to {$scooterId}: " . $e->getMessage() . "\n";
            $this->cleanupConnection($scooterId);
            return false;
        }
    }

    protected function cleanupConnection($scooterId)
    {
        if (isset($this->scooterAddressMap[$scooterId])) {
            $address = $this->scooterAddressMap[$scooterId];
            unset($this->connections[$address]);
        }
        unset($this->scooterConnections[$scooterId]);
        unset($this->scooterAddressMap[$scooterId]);
        echo "[CLEANUP] Removed scooter {$scooterId} from connections\n";
    }

    public function getConnectedScooters()
    {
        return array_keys($this->scooterConnections);
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
