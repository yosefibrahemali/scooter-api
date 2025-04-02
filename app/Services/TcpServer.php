<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;


class TcpServer
{
    protected $host = "0.0.0.0"; // الاستماع على جميع عناوين IP
    protected $port = 5000;      // رقم المنفذ
    protected $conn = null;      // Store the connection

    public function start()
    {
        $socket = stream_socket_server("tcp://$this->host:$this->port", $errno, $errstr);

        if (!$socket) {
            die("❌ فشل تشغيل السيرفر: $errstr ($errno)\n");
        }

        echo "🔵 خادم TCP يعمل على {$this->host}:{$this->port}...\n";

        while (true) {
            $this->conn = @stream_socket_accept($socket, 10); // انتظار 10 ثوانٍ قبل المهلة

            if ($this->conn) {
                $clientData = fread($this->conn, 1024);
                $clientData = trim($clientData);
                echo "📩 استقبلنا اتصال جديد: " . $clientData . "\n";

                // Here we just listen to the incoming data, but we don't send the command automatically
                if (strpos($clientData, "*SCOR") !== false) {
                    echo "✅ استلمنا أمر من السكوتر\n";
                }

                fclose($this->conn);
            } else {
                echo "⏳ لم يتم استقبال أي اتصال، الاستمرار في الاستماع...\n";
            }
        }

        fclose($socket);
    }

    // This method will now be called explicitly by the controller to send the unlock command
    public function sendUnlockCommand($imei)
    {
        if ($this->conn) {
            $key = 20;  // Example key value
            $userId = 1234;  // Example user ID
            $timestamp = time();  // Current Unix timestamp

            // Send the R0 command to get the KEY
            $this->sendR0UnlockCommand($imei, $key, $userId, $timestamp);
        } else {
            echo "❌ لا يوجد اتصال حاليًا.\n";
        }
    }

    // Send the R0 command to unlock and generate the KEY
    private function sendR0UnlockCommand($imei, $key, $userId, $timestamp)
    {
        // Format the R0 unlock command
        $command = "*SCOS,OM,{$imei},R0,0,{$key},{$userId},{$timestamp}#\n";

        // Send the unlock command to the scooter to get the operation KEY
        fwrite($this->conn, $command);
        echo "🚀 تم إرسال الأمر R0: $command\n";

        // Now send the L0 unlock command with the KEY
        $this->sendL0UnlockCommand($imei, $key, $userId, $timestamp);
    }

    // Send the L0 unlock command
    private function sendL0UnlockCommand($imei, $key, $userId, $timestamp)
    {
        // Format the L0 unlock command
        $command = "*SCOS,OM,{$imei},L0,{$key},{$userId},{$timestamp}#\n";

        // Send the L0 unlock command to unlock the scooter
        fwrite($this->conn, $command);
        echo "🚀 تم إرسال الأمر L0: $command\n";
    }
}







