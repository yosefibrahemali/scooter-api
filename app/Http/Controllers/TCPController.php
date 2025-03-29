<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use React\Socket\Server;
use React\EventLoop\Factory;

class TCPController extends Controller
{
    public function startServer()
    {
        $loop = Factory::create();
        $server = new Server('0.0.0.0:9000', $loop);

        $server->on('connection', function ($connection) {
            echo "🛴 Scooter Connected!\n";

            $connection->on('data', function ($data) use ($connection) {
                echo "📩 Received Data: " . bin2hex($data) . "\n";

                // هنا يتم فحص البيانات المستلمة، إذا كانت السكوتر يطلب فتح القفل:
                if ($data === hex2bin('01020304')) { // تحقق من الأمر الذي يرسله السكوتر
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

