<?php

require 'vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;

// إنشاء الحلقة التكرارية
$loop = Factory::create();

// إعداد الخادم للاستماع على 0.0.0.0 على المنفذ 16994
$server = new Server('0.0.0.0:16994', $loop);

$server->on('connection', function ($connection) {
    echo "🛴 Scooter Connected!\n";

    $connection->on('data', function ($data) use ($connection) {
        echo "📩 Received Data: " . bin2hex($data) . "\n";

        // تحقق مما إذا كان الطلب لفتح القفل
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

// تشغيل الخادم
echo "🔧 Listening on tcp://0.0.0.0:16994\n";
$loop->run();
