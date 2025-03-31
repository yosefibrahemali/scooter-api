<?php

namespace App\Services;

use App\Models\ScooterConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TcpServer
{
    protected $host = '0.0.0.0';
    protected $port = 3000;

    public function start()
    {
        set_time_limit(0);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($socket, $this->host, $this->port);
        socket_listen($socket);

        Log::info("TCP Server started on {$this->host}:{$this->port}");

        while (true) {
            $client = socket_accept($socket);
            $clientIP = $this->getClientIP($client);

            // Save connection to database
            ScooterConnection::updateOrCreate(
                ['scooter_ip' => $clientIP],
                ['connected_at' => now(), 'disconnected_at' => null]
            );

            Log::info("Scooter Connected: " . $clientIP);

            $input = socket_read($client, 1024);
            Log::info("Received from $clientIP: " . trim($input));

            $response = "ACK";
            socket_write($client, $response, strlen($response));

            // Update disconnection time
            ScooterConnection::where('scooter_ip', $clientIP)
                ->update(['disconnected_at' => now()]);

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
