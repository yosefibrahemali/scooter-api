<?php

namespace App\Services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class TcpServer implements MessageComponentInterface
{
    protected $connections = [];

    public function onOpen(ConnectionInterface $conn)
    {
        echo "✅ New connection: {$conn->resourceId}\n";
        $this->connections[$conn->resourceId] = $conn;
    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        echo "📩 Received data: $msg\n";

        if (preg_match('/\*SCOR,OM,(\d+),/', $msg, $matches)) {
            $imei = $matches[1];
            echo "🔗 IMEI Detected: $imei\n";
            $this->connections[$imei] = $conn;
            echo "✅ Connection stored for IMEI: $imei\n";
        }

        // رد تلقائي (اختياري)
        $conn->send("✅ Server received your message!");
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "❌ Connection closed: {$conn->resourceId}\n";
        unset($this->connections[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "⚠️ Error: {$e->getMessage()}\n";
        $conn->close();
    }

    public function sendUnlockCommand($imei)
    {
        if (!isset($this->connections[$imei])) {
            return "⚠️ No active connection found for IMEI: $imei";
        }

        $conn = $this->connections[$imei];
        $command = "*SCOS,OM,{$imei},L0,55,1234," . time() . "#\n";
        $conn->send($command);

        echo "🚀 Sent unlock command to IMEI {$imei}: $command\n";
        return "✅ Unlock command sent to IMEI: $imei";
    }
}
