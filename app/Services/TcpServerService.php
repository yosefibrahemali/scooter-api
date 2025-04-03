<?php

namespace App\Services;

use React\Socket\ConnectionInterface;
use React\Socket\TcpServer;
use React\EventLoop\Factory;

class TcpServerService
{
    protected static $instance = null;
    protected $loop;
    protected $server;
    protected $connections = [];
    protected $scooterConnections = [];
    protected $scooterAddressMap = [];
    protected $isRunning = false;

    public function __construct()
    {
        $this->loop = Factory::create();
        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance ?? new self();
    }

    public function start($port = 5000)
    {
        if ($this->isRunning) {
            return;
        }

        $this->isRunning = true;
        $this->server = new TcpServer("0.0.0.0:$port", $this->loop);

        $this->server->on('connection', function (ConnectionInterface $connection) {
            $remoteAddress = $connection->getRemoteAddress();
            $this->connections[$remoteAddress] = $connection;

            $connection->on('data', function ($data) use ($connection, $remoteAddress) {
                $this->handleIncomingData($data, $connection, $remoteAddress);
            });

            $connection->on('close', function () use ($remoteAddress) {
                $this->handleDisconnection($remoteAddress);
            });
        });

        echo "Server running on port {$port}\n";
        $this->loop->run();
    }

    public function isRunning()
    {
        return $this->isRunning;
    }


    

    
    protected function handleDisconnection($remoteAddress)
    {
        // البحث عن معرف السكوتر المرتبط بعنوان الاتصال
        $scooterId = array_search($remoteAddress, $this->scooterAddressMap);
        
        if ($scooterId !== false) {
            echo "[DISCONNECT] Scooter {$scooterId} disconnected\n";
            unset($this->scooterConnections[$scooterId]);
            unset($this->scooterAddressMap[$scooterId]);
        }
        
        if (isset($this->connections[$remoteAddress])) {
            unset($this->connections[$remoteAddress]);
        }
        
        echo "[STATUS] Active connections: " . count($this->connections) . "\n";
    }


    protected function handleIncomingData($data, $connection, $remoteAddress)
    {
        $cleanData = trim($data);
        echo "Raw data from {$remoteAddress}: " . bin2hex($cleanData) . "\n";

        if (preg_match('/\*SCOR,OM,(\d{15}),([^,#]+)/', $cleanData, $matches)) {
            $scooterId = $matches[1];
            $commandType = $matches[2];

            // تحديث بيانات الاتصال
            $this->updateConnection($scooterId, $connection, $remoteAddress);

            switch ($commandType) {
                case 'Q0':
                    echo $cleanData;
                    break;
                // ... معالجات أخرى ...
                default:
                    echo $cleanData;
            }

            $connection->write("*ACK,{$scooterId},{$commandType}#\n");
        } else {
            echo $connection, $remoteAddress, $cleanData;
        }
    }


    protected function updateConnection($scooterId, $connection, $remoteAddress)
    {
        $this->scooterConnections[$scooterId] = $connection;
        $this->scooterAddressMap[$scooterId] = $remoteAddress;
        $this->connections[$remoteAddress] = $connection;
        
        echo "[ACTIVE] Scooter {$scooterId} at {$remoteAddress}\n";
        echo "[CONNECTIONS] Total: " . count($this->scooterConnections) . "\n";
    }

    public function sendCommandToScooter($scooterId, $command)
    {
        if (!isset($this->scooterConnections[$scooterId])) {
            echo "[ERROR] Scooter {$scooterId} not in connections\n";
            echo "[ACTIVE_SCOOTERS] " . implode(', ', array_keys($this->scooterConnections)) . "\n";
            return false;
        }

        try {
            $this->scooterConnections[$scooterId]->write($command);
            echo "[SENT] Command to {$scooterId}: {$command}";
            return true;
        } catch (\Exception $e) {
            $this->cleanupConnection($scooterId);
            echo "[SEND_ERROR] {$scooterId}: " . $e->getMessage() . "\n";
            return false;
        }
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
