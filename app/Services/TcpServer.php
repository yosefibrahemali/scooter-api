<?php
namespace App\Services;

use React\Socket\Server;
use React\EventLoop\Factory;

class TcpServer
{
    public function start()
    {
        $loop = Factory::create();
        $server = new Server('0.0.0.0:16994', $loop);

        $server->on('connection', function ($connection) {
            echo "🛴 Scooter Connected!\n";

            $connection->on('data', function ($data) use ($connection) {
                echo "📩 Received Data: " . bin2hex($data) . "\n";

                // تحقق مما إذا كان الطلب فتح القفل
                if ($data === hex2bin('01020304')) { // استبدل بالكود الصحيح من البروتوكول
                    $unlockCommand = hex2bin('AABBCCDD'); // استبدل بالكود الصحيح لفتح القفل
                    $connection->write($unlockCommand);
                    echo "✅ Unlock command sent!\n";
                }
            });

            $connection->on('close', function () {
                echo "🔌 Scooter Disconnected!\n";
            });
        });

        echo "🚀 TCP Server Started on port 9000...\n";
        $loop->run();
    }
}

