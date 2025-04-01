<?php

namespace App\Services;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

class TcpService
{
    private static $scooterConnections = [];

    // تشغيل Workerman TCP Server
    public static function startServer()
    {
        $tcp_server = new Worker("tcp://0.0.0.0:3000");

        $tcp_server->onConnect = function (TcpConnection $connection) {
            echo "🔗 Scooter Connected: " . $connection->getRemoteIp() . "\n";
            self::$scooterConnections['default_imei'] = $connection;
        };

        $tcp_server->onMessage = function (TcpConnection $connection, $data) {
            echo "📩 Received: " . $data . "\n";

            if (preg_match('/\d{15}/', $data, $matches)) {
                $imei = $matches[0];
                self::$scooterConnections[$imei] = $connection;
                echo "✅ Registered IMEI: " . $imei . "\n";
            }
        };

        Worker::runAll();
    }

    // إرسال أمر إلى السكوتر
    public static function sendCommand($imei, $commandType = 'R0', $value = 0)
    {
        if (!isset(self::$scooterConnections[$imei])) {
            return "❌ Scooter $imei not connected!";
        }

        $connection = self::$scooterConnections[$imei];
        $command = "*SCOS,OM,{$imei},{$commandType},{$value},20,1234," . time() . "#\n";
        $connection->send($command);
        
        return "🚀 Command sent to Scooter $imei: $command";
    }
}
