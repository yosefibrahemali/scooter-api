<?php

require 'vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;

// إنشاء الحلقة التكرارية
// إنشاء الحلقة التكرارية


use React\Socket\Connection;

$loop = Factory::create();

// إعداد الخادم للاستماع على 0.0.0.0:3000
$server = new Server('0.0.0.0:3000', $loop);

$server->on('connection', function (Connection $connection) use ($loop) {
    echo "🛴 Scooter Connected!\n";

    // إرسال أمر فتح القفل مباشرة بعد الاتصال
    $unlockCommand = hex2bin('AABBCCDD') . "\r\n"; // تأكد من الكود الصحيح وأضف \r\n
    $connection->write($unlockCommand);
    echo "✅ Unlock command sent after connection!\n";

    // إرسال رسالة "keep-alive" كل 10 ثوانٍ للحفاظ على الاتصال
    $loop->addPeriodicTimer(10, function () use ($connection) {
        $keepAlive = hex2bin('AA55'); // استبدل بالكود الصحيح من البروتوكول
        $connection->write($keepAlive);
        echo "🔄 Sent keep-alive message\n";
    });

    // الاستماع للبيانات الواردة من السكوتر
    $connection->on('data', function ($data) use ($connection) {
        echo "📩 Received Data: " . bin2hex($data) . "\n";

        // معالجة الاستجابة (تأكد من فحص البيانات بشكل مناسب)
        if (bin2hex($data) === 'expected_unlock_response') { // استبدل بالقيمة الصحيحة
            echo "✅ Scooter unlocked successfully!\n";
        }
    });

    // عند إغلاق الاتصال
    $connection->on('close', function () {
        echo "🔌 Scooter Disconnected!\n";
    });
});

// تشغيل الخادم
echo "🔧 Listening on tcp://0.0.0.0:3000\n";
$loop->run();


// public function startScooter()
// {
//     $host = '138.199.198.151';
//     $port = '3000';
//     $timeout = 3; // تقليل وقت الانتظار

   
//     $context = stream_context_create([
//         'socket' => ['connect_timeout' => 5]
//     ]);

//     $socket = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);

//     if (!$socket) {
//         return response()->json([
//             'success' => false,
//             'message' => "فشل الاتصال بالسكوتر: $errstr ($errno)"
//         ], 500);
//     }

//     stream_set_timeout($socket, 3);

//     $command = "*SCOS,OM,868351077123154,S6#\n";
//     fwrite($socket, $command);

//     $response = fread($socket, 1024);
//     fclose($socket);

//     return response()->json([
//         'success' => true,
//         'message' => "تم إرسال أمر تشغيل السكوتر",
//         'response' => trim($response)
//     ]);
    
// }

?>
