<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TcpServer extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'Start the TCP server for scooter communication';

    public function handle()
    {
        $serverIp = '0.0.0.0';
        $serverPort = 3000;

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $this->error("Failed to create socket: " . socket_strerror(socket_last_error()));
            return;
        }

        if (!socket_bind($socket, $serverIp, $serverPort)) {
            $this->error("Failed to bind socket: " . socket_strerror(socket_last_error()));
            return;
        }

        if (!socket_listen($socket)) {
            $this->error("Failed to listen on socket: " . socket_strerror(socket_last_error()));
            return;
        }

        $this->info("TCP server is running on $serverIp:$serverPort");

        while (true) {
            $clientSocket = socket_accept($socket);
            if ($clientSocket === false) {
                $this->error("Failed to accept client connection: " . socket_strerror(socket_last_error()));
                continue;
            }

            $data = '';
            while ($buffer = socket_read($clientSocket, 1024)) {
                $data .= $buffer;
                if (strpos($data, "\n") !== false) {
                    break;
                }
            }

            $this->info("Received data: $data");

            $response = $this->processCommand($data);
            socket_write($clientSocket, $response, strlen($response));
            socket_close($clientSocket);
        }

        socket_close($socket);
    }

    private function processCommand($command)
    {
        if (strpos($command, 'H0') !== false) {
            return "*SCOS,OM,123456789123456,H0#<LF>\n";
        }

        return "*SCOS,OM,123456789123456,INVALID#<LF>\n";
    }
}