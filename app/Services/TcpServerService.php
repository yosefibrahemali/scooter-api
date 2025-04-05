<?php

namespace App\Services;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer;
use Illuminate\Support\Facades\Redis;
use React\EventLoop\Loop;


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
        if ($this->isRunning) return;

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

        $this->listenForRedisCommands();

        echo "Server running on port {$port}\n";
        $this->loop->run();
    }

    public function isRunning()
    {
        return $this->isRunning;
    }

    protected function handleDisconnection($remoteAddress)
    {
        $scooterId = array_search($remoteAddress, $this->scooterAddressMap);
        if ($scooterId !== false) {
            echo "[DISCONNECT] Scooter {$scooterId} disconnected\n";
            unset($this->scooterConnections[$scooterId]);
            unset($this->scooterAddressMap[$scooterId]);
        }

        unset($this->connections[$remoteAddress]);
        echo "[STATUS] Active connections: " . count($this->connections) . "\n";
    }

    protected function handleIncomingData($data, $connection, $remoteAddress)
    {
        $cleanData = trim($data);
        echo "Raw data from {$remoteAddress}: " . bin2hex($cleanData) . "\n";

        if (preg_match('/\*SCOR,OM,(\d{15}),([^,#]+)/', $cleanData, $matches)) {
            $scooterId = $matches[1];
            $commandType = $matches[2];

            $this->updateConnection($scooterId, $connection, $remoteAddress);

            $connection->write("*ACK,{$scooterId},{$commandType}#\n");
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

    public function listenForRedisCommands()
    {
        $redisClient = new Factory($this->loop);
        $redisClient->createLazyClient('redis://127.0.0.1:6379')->then(function ($client) {
            $client->on('message', function ($channel, $message) {
                echo "[REDIS] Message on {$channel}: {$message}\n";

                $data = json_decode($message, true);
                if (isset($data['command']) && isset($data['scooterId'])) {
                    $this->handleRedisCommand($data['scooterId'], $data['command'], $data['userId'] ?? '1234');
                }
            });

            $client->subscribe('scooter-commands');
        });
    }

    public function handleRedisCommand($scooterId, $command, $userId = '1234')
    {
        switch ($command) {
            case 'unlock':
                $this->unlockScooter($scooterId, $userId);
                break;
        }
    }

    public function unlockScooter($scooterId, $userId)
    {
        $timestamp = time();

        $r0Command = "*SCOS,OM,{$scooterId},R0,0,20,{$userId},{$timestamp}#\n";
        $this->sendCommandToScooter($scooterId, $r0Command);

        // Delay sending L0 slightly to give R0 time
        $this->loop->addTimer(1.5, function () use ($scooterId, $userId, $timestamp) {
            $l0Command = "*SCOS,OM,{$scooterId},L0,55,{$userId},{$timestamp}#\n";
            $this->sendCommandToScooter($scooterId, $l0Command);
        });
    }
}
