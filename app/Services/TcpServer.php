<?php

use Workerman\Worker;

require_once __DIR__ . '/../../vendor/autoload.php';

// إنشاء سيرفر TCP على المنفذ 3000
$tcp_server = new Worker("tcp://0.0.0.0:3000");

// عند اتصال جهاز جديد
$tcp_server->onConnect = function ($connection) {
    echo "🔗 Scooter Connected: " . $connection->getRemoteIp() . "\n";
};

// عند استقبال بيانات من السكوتر
$tcp_server->onMessage = function ($connection, $data) {
    echo "📩 Received from Scooter: " . $data . "\n";
    
    // إرسال رد إلى السكوتر (اختياري)
    $connection->send("Message received");
};

// تشغيل السيرفر
Worker::runAll();
