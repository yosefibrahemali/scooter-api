<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TcpServer
{
    protected $host = "0.0.0.0"; // الاستماع على جميع عناوين IP
    protected $port = 5000;      // رقم المنفذ

    public function start()
    {
        $socket = stream_socket_server("tcp://$this->host:$this->port", $errno, $errstr);

        if (!$socket) {
            die("❌ فشل تشغيل السيرفر: $errstr ($errno)\n");
        }

        echo "🔵 خادم TCP يعمل على {$this->host}:{$this->port}...\n";

        while (true) {
            $conn = @stream_socket_accept($socket, 10); // انتظار 10 ثوانٍ قبل المهلة
            
            if ($conn) {
                $clientData = fread($conn, 1024); 
                $clientData = trim($clientData);
                echo "📩 استقبلنا اتصال جديد: " . $clientData . "\n";

                // تحليل البيانات القادمة من السكوتر
                if (strpos($clientData, "*SCOS") !== false) {
                    echo "✅ استلمنا أمر من السكوتر\n";

                    // بناء الأمر الذي سيتم إرساله
                    $command = $this->sendCommand($clientData);
                    
                    // إرسال الأمر إلى السكوتر
                    fwrite($conn, $command);
                    echo "🚀 تم إرسال الأمر: $command\n";

                    // استلام الرد من السكوتر
                    $response = fread($conn, 1024);
                    echo "📩 الرد من السكوتر: $response\n";
                }

                fclose($conn);
            } else {
                echo "⏳ لم يتم استقبال أي اتصال، الاستمرار في الاستماع...\n";
            }
        }

        fclose($socket);
    }

    public function sendCommand($clientData)
    {
        // افترضنا أن البيانات تأتي مع الأمر من السكوتر
        // هذا هو المكان الذي يمكنك فيه تخصيص الأوامر بناءً على نوع البيانات المستلمة

        // مثال: استخراج IMEI أو نوع الأمر من البيانات القادمة
        preg_match('/\*SCOS,OM,(\d+),/', $clientData, $matches);
        $imei = $matches[1] ?? '868351077123154';  // استخدم IMEI من البيانات أو قيمة افتراضية

        // حدد نوع الأمر المطلوب إرساله
        if (strpos($clientData, "R0") !== false) {
            // مثال: أمر فتح القفل (Unlock)
            return $this->unlockCommand($imei);
        } else {
            // أمر آخر مثل قفل الدراجة (Lock)
            return $this->lockCommand($imei);
        }
    }

    public function unlockCommand($imei)
    {
        // هنا يمكنك تخصيص الأمر لفتح القفل
        $timestamp = time();  // الوقت الحالي بالثواني
        $key = 20;  // مفتاح عشوائي للإلغاء
        $userId = 1234;  // معرف المستخدم

        // بناء الأمر للإرسال
        $command = "*SCOS,OM,{$imei},R0,0,{$key},{$userId},{$timestamp}#\n";
        return $command;
    }

    public function lockCommand($imei)
    {
        // هنا يمكنك تخصيص الأمر لقفل الدراجة
        $timestamp = time();  // الوقت الحالي بالثواني
        $key = 30;  // مفتاح عشوائي للقفل
        $userId = 1234;  // معرف المستخدم

        // بناء الأمر للإرسال
        $command = "*SCOS,OM,{$imei},R1,0,{$key},{$userId},{$timestamp}#\n";
        return $command;
    }
}
