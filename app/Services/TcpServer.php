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
                    echo "✅ استلمنا أمر فتح القفل من الخادم\n";
                
                    // استخراج IMEI من البيانات المستلمة
                    $parts = explode(',', $clientData);
                    $imei = $parts[2] ?? 'UNKNOWN'; // رقم IMEI يكون في الجزء الثالث من الرسالة
                
                    // إرسال تأكيد إلى السكوتر
                    $confirmation = "*ACK,OM,{$imei},R0#"; // رد تأكيد العملية
                    fwrite($conn, $confirmation . "\n");
                
                    echo "🚀 تم إرسال التأكيد إلى السكوتر: $confirmation\n";
                }
                
                

                fclose($conn);
            } else {
                echo "⏳ لم يتم استقبال أي اتصال، الاستمرار في الاستماع...\n";
            }
        }

        fclose($socket);
    }
}
