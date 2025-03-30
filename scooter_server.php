<?php

require 'vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;

// إنشاء الحلقة التكرارية
// إنشاء الحلقة التكرارية

require 'vendor/autoload.php'; // Ensure you have the correct path to your autoload file

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\Connection;

$loop = Factory::create();

// Setup the server to listen on 0.0.0.0:3000
$server = new Server('0.0.0.0:3000', $loop);

$server->on('connection', function (Connection $connection) use ($loop) {
    echo "🛴 Scooter Connected!\n";

    // Send a "keep-alive" message every 10 seconds to keep the connection alive
    $loop->addPeriodicTimer(10, function () use ($connection) {
        $keepAlive = hex2bin('AA55'); // Replace with the correct code for keeping the connection alive
        $connection->write($keepAlive);
        echo "🔄 Sent keep-alive message\n";
    });

    // Send the unlock command immediately after the connection is established
    $unlockCommand = hex2bin('AABBCCDD') . "\r\n"; // Ensure the correct unlock command with line ending
    $connection->write($unlockCommand);
    echo "✅ Unlock command sent after connection!\n";

    // Listen for incoming data
    $connection->on('data', function ($data) use ($connection) {
        echo "📩 Received Data: " . bin2hex($data) . "\n";

        // Handle the received data (e.g., log it or check if it's a response you need)
        // For now, you can add more conditions to check specific responses from the scooter
    });

    // When the connection is closed, notify that the scooter is disconnected
    $connection->on('close', function () {
        echo "🔌 Scooter Disconnected!\n";
    });
});

// Run the server
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

