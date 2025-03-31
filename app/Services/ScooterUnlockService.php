<?php

namespace App\Services;

class ScooterUnlockService
{
    protected $host = '138.199.198.151/'; // عنوان الـ IP الخاص بالسكوتر
    protected $port = 3000; // المنفذ المستخدم في الاتصال

    public function unlockScooter($imei)
    {
        // تجهيز الأمر المطلوب
        $command = "*SCOS,OM,{$imei},R0,0,20,1234," . time() . "#\n";

        // محاولة فتح الاتصال باستخدام بروتوكول TCP
        $socket = @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, 10);

        if (!$socket) {
            return "خطأ في الاتصال: $errstr ($errno)";
        }

        // إرسال الأمر إلى السكوتر
        fwrite($socket, $command);

        // استقبال الاستجابة (إذا كان هناك رد من الجهاز)
        $response = fread($socket, 1024);

        // إغلاق الاتصال
        fclose($socket);

        return $response ?: "تم إرسال الأمر بنجاح!";
    }
}