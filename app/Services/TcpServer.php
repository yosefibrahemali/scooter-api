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
                if (strpos($clientData, "*SCOR") !== false) {
                    echo "✅ استلمنا أمر من السكوتر\n";

                    // Check if it's an unlock request (R0)
                    if (strpos($clientData, "R0") !== false) {
                        // If unlock command is detected, respond with unlock confirmation
                        $imei = '868351077123154'; // Example IMEI from the incoming message
                        $this->sendUnlockCommand($conn, $imei);
                    }
                }

                fclose($conn);
            } else {
                echo "⏳ لم يتم استقبال أي اتصال، الاستمرار في الاستماع...\n";
            }
        }

        fclose($socket);
    }

    public function sendUnlockCommand($conn, $imei)
    {
        // Prepare the unlock command (R0)
        $key = 20;  // Example key for unlocking (you can change it as needed)
        $userId = 1234;  // Example user ID
        $timestamp = time();  // Current Unix timestamp
        
        // Format the unlock command
        $command = "*SCOS,OM,{$imei},R0,0,{$key},{$userId},{$timestamp}#\n";
        
        // Send the unlock command to the scooter
        fwrite($conn, $command);
        echo "🚀 تم إرسال الأمر: $command\n";
    }
}





