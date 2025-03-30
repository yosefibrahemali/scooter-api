<?php

use React\Socket\Server;
use React\EventLoop\Factory;
use React\Socket\Connection;

require 'vendor/autoload.php';  // تأكد من تحميل مكتبة ReactPHP عبر Composer

$loop = Factory::create();

// إعداد الخادم للاستماع على 0.0.0.0:3000
$server = new Server('0.0.0.0:3000', $loop);

$server->on('connection', function (Connection $connection) use ($loop) {
    echo "🛴 Scooter Connected!\n";

    // إرسال "keep-alive" كل 10 ثوانٍ للحفاظ على الاتصال
    $loop->addPeriodicTimer(10, function () use ($connection) {
        $keepAlive = hex2bin('AA55'); // استبدل بالكود الصحيح للمحافظة على الاتصال
        $connection->write($keepAlive);
        echo "🔄 Sent keep-alive message\n";
    });

    // إرسال أمر فتح القفل مباشرة بعد الاتصال
    $unlockCommand = "*SCOS,OM,868351077123154,R0,0,20,1234," . round(microtime(true) * 1000) . "#\n"; // الأمر مع الوقت الحالي
    $connection->write($unlockCommand);
    echo "✅ Unlock command sent after connection!\n";

    // الاستماع للبيانات الواردة
    $connection->on('data', function ($data) use ($connection) {
        echo "📩 Received Data: " . bin2hex($data) . "\n";

        // التعامل مع البيانات الواردة (مثال: تسجيل البيانات أو فحص الاستجابة المطلوبة)
        // يمكنك إضافة شروط إضافية للتحقق من الاستجابة المطلوبة من السكوتر
    });

    // عندما يتم إغلاق الاتصال، إخطار بأنه تم قطع الاتصال
    $connection->on('close', function () {
        echo "🔌 Scooter Disconnected!\n";
    });
});

// تشغيل الخادم
echo "🔧 Listening on tcp://0.0.0.0:3000\n";
$loop->run();

