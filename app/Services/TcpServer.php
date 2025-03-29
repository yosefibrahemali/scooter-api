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

    public function sendUnlockCommand($ip, $port)
    {
        // إعداد البيانات المرسلة وفقًا للبروتوكول
        $STX = "\xA3\xA4"; // رأس الإطار
        $LEN = "\x05"; // طول البيانات
        $RAND = random_bytes(1); // رقم عشوائي للأمان
        $KEY = "\x34"; // مفتاح الاتصال (يجب تغييره بناءً على القفل)
        $CMD = "\x05"; // أمر فتح القفل
        $DATA = "\x01"; // نجاح
        $TIMESTAMP = "\x00\x00\x00\x01"; // توقيت زمني افتراضي
        $CRC = "\x00"; // يجب حساب CRC8 الصحيح

        // دمج البيانات في سلسلة واحدة
        $command = $STX . $LEN . $RAND . $KEY . $CMD . $DATA . $TIMESTAMP . $CRC;

        // إنشاء اتصال TCP
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            return "❌ فشل إنشاء الاتصال: " . socket_strerror(socket_last_error());
        }

        // الاتصال بالقفل
        $result = socket_connect($socket, $ip, $port);
        if ($result === false) {
            return "❌ فشل الاتصال بالقفل: " . socket_strerror(socket_last_error($socket));
        }

        // إرسال البيانات
        socket_write($socket, $command, strlen($command));

        // استقبال الرد من القفل
        $response = socket_read($socket, 1024);
        socket_close($socket);

        return "🔓 رد القفل: " . bin2hex($response);
    }
}

