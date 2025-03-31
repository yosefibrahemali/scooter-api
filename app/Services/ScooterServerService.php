<?php

namespace App\Services;

class ScooterServerService
{
    protected $host = '0.0.0.0'; // يستمع على جميع الاتصالات
    protected $port = 3000;
    protected static $clients = []; // قائمة بالأجهزة المتصلة

    public function startServer()
    {
        // إنشاء خادم TCP
        $socket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);

        if (!$socket) {
            die("خطأ في فتح السيرفر: $errstr ($errno)\n");
        }

        echo "🚀 السيرفر يعمل على {$this->host}:{$this->port}...\n";

        while ($conn = stream_socket_accept($socket)) {
            stream_set_blocking($conn, false); // عدم حظر الاتصالات الأخرى
            $request = fread($conn, 1024);

            // استخراج IMEI من الطلب وتخزين الاتصال
            if (preg_match('/\*SCOS,OM,(\d+),/', $request, $matches)) {
                $imei = $matches[1];
                self::$clients[$imei] = $conn; // حفظ الاتصال مع السكوتر
                echo "📌 السكوتر ($imei) متصلة الآن.\n";
            }

            fwrite($conn, "OK\n"); // تأكيد الاستلام
        }

        fclose($socket);
    }

    // إرسال أمر إلى السكوتر
    public static function sendCommandToScooter($imei, $command)
    {
        if (!isset(self::$clients[$imei])) {
            return "⚠️ السكوتر غير متصلة!";
        }

        $conn = self::$clients[$imei];
        fwrite($conn, $command . "\n");
        return "✅ تم إرسال الأمر إلى السكوتر ($imei)!";
    }
}