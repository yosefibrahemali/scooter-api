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

                // Checking if the incoming message is *SCOR from the scooter
                if (strpos($clientData, "*SCOR") !== false) {
                    echo "✅ استلمنا أمر من السكوتر\n";
                    
                    // Now send the R0 unlock command (to get the KEY)
                    $imei = '868351077123154';  // Example IMEI
                    $this->sendR0UnlockCommand($conn, $imei);
                }

                fclose($conn);
            } else {
                echo "⏳ لم يتم استقبال أي اتصال، الاستمرار في الاستماع...\n";
            }
        }

        fclose($socket);
    }

    // Send R0 command to unlock and generate the KEY
    public function sendR0UnlockCommand($conn, $imei)
    {
        $key = 20;  // Example key value
        $userId = 1234;  // Example user ID
        $timestamp = time();  // Current Unix timestamp

        // Format the R0 unlock command to get the operation KEY
        $command = "*SCOS,OM,{$imei},R0,0,{$key},{$userId},{$timestamp}#\n";

        // Send the unlock command to the scooter to get the operation KEY
        fwrite($conn, $command);
        echo "🚀 تم إرسال الأمر R0: $command\n";

        // After receiving the response (to get the key), send the L0 unlock command
        $this->sendL0UnlockCommand($conn, $imei, $key, $userId, $timestamp);
    }

    // Send the L0 unlock command with the KEY received from the R0 command
    public function sendL0UnlockCommand($conn, $imei, $key, $userId, $timestamp)
    {
        // Format the L0 unlock command
        $command = "*SCOS,OM,{$imei},L0,{$key},{$userId},{$timestamp}#\n";

        // Send the L0 unlock command to unlock the scooter
        fwrite($conn, $command);
        echo "🚀 تم إرسال الأمر L0: $command\n";
    }
}






