<?php
require 'vendor/autoload.php'; // تأكد من المسار الصحيح

use React\EventLoop\Factory;
use React\Socket\Server;
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
    
    // إضافة تأخير بين الأوامر
    sleep(1); // تأخير 1 ثانية

    // إرسال رسالة keep-alive كل 10 ثوانٍ للحفاظ على الاتصال
    $loop->addPeriodicTimer(10, function () use ($connection) {
        $keepAlive = hex2bin('AA55'); // استبدل بالكود الصحيح من البروتوكول
        $connection->write($keepAlive);
        echo "🔄 Sent keep-alive message\n";
    });

    // الاستماع للبيانات الواردة من السكوتر
    $connection->on('data', function ($data) use ($connection) {
        echo "📩 Received Data: " . bin2hex($data) . "\n"; // استخدم bin2hex لعرض البيانات بشكل Hex
        echo "Data as string: " . $data . "\n"; // طباعة البيانات كسلسلة نصية

        // معالجة الاستجابة
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
?>
