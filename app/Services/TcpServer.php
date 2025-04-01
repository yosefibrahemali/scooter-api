<?php

use Workerman\Worker;

require_once __DIR__ . '/../../vendor/autoload.php';

// إنشاء TCP Server يستمع على المنفذ المطلوب
$tcp_server = new Worker("tcp://0.0.0.0:8080");

// الحد الأقصى للاتصالات المتزامنة
$tcp_server->count = 4;

// عند اتصال جهاز جديد (السكوتر)
$tcp_server->onConnect = function ($connection) {
    echo "تم الاتصال بجهاز جديد: {$connection->getRemoteIp()}\n";
};

// استقبال البيانات من السكوتر
$tcp_server->onMessage = function ($connection, $data) {
    echo "تم استقبال البيانات: " . bin2hex($data) . "\n";
    
    // معالجة الأوامر القادمة من السكوتر
    if (trim($data) == 'unlock') {
        $response = "فتح القفل\n";
    } else {
        $response = "أمر غير معروف\n";
    }

    // إرسال الرد إلى السكوتر
    $connection->send($response);
};

// عند قطع الاتصال
$tcp_server->onClose = function ($connection) {
    echo "تم قطع الاتصال بجهاز.\n";
};

// تشغيل السيرفر
Worker::runAll();
