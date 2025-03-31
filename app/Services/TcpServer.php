<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TcpServer
{
    protected $host = '0.0.0.0';
    protected $port = 3000;
    protected $clients = [];

    public function start()
    {
        set_time_limit(0);

        // Create and bind socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($socket, $this->host, $this->port);
        socket_listen($socket);

        Log::info("TCP Server started on {$this->host}:{$this->port}");

        while (true) {
            $client = socket_accept($socket);
            $clientIP = $this->getClientIP($client);

            // Add client to active list
            $this->clients[$clientIP] = [
                'ip' => $clientIP,
                'connected_at' => now(),
            ];

            Log::info("Scooter Connected: " . $clientIP);

            $input = socket_read($client, 1024);
            Log::info("Received from $clientIP: " . trim($input));

            // Send response
            $response = "ACK";
            socket_write($client, $response, strlen($response));

            // Remove client when disconnected
            unset($this->clients[$clientIP]);
            Log::info("Scooter Disconnected: " . $clientIP);

            socket_close($client);
        }

        socket_close($socket);
    }

    private function getClientIP($client)
    {
        socket_getpeername($client, $address);
        return $address;
    }
}
