<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScooterController 
{
    protected $host = "0.0.0.0"; // IP address of the server
    protected $port = 5000;      // TCP Port
    protected $conn = null;      // Connection variable

    // Method to establish the connection
    public function connectToScooter($imei)
    {
        try {
            // Create TCP connection to the server
            $this->conn = stream_socket_client("tcp://$this->host:$this->port", $errno, $errstr);

            if (!$this->conn) {
                return response()->json(['message' => '❌ فشل الاتصال بالخادم: ' . $errstr], 500);
            }

            return response()->json(['message' => '✅ تم الاتصال بالخادم بنجاح!']);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error establishing connection: ' . $e->getMessage());
            return response()->json(['message' => '❌ حدث خطأ أثناء الاتصال.'], 500);
        }
    }

    // Method to send unlock command
    public function sendUnlockCommand($imei)
    {
        if (!$this->conn) {
            return response()->json(['message' => '❌ الاتصال غير مفعل! يجب الاتصال أولاً.'], 500);
        }

        try {
            // Unlock command (R0) - Send to generate KEY
            $this->sendR0UnlockCommand($imei);

            // Close the connection after sending the command
            fclose($this->conn);
            $this->conn = null;  // Reset the connection

            return response()->json([
                'message' => '✅ تم إرسال أمر الفتح بنجاح!',
                'command' => "*SCOS,OM,{$imei},R0,0,20,1234," . time() . "#\n",
                'response' => 'Your response from the scooter here'
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error sending unlock command: ' . $e->getMessage());
            return response()->json(['message' => '❌ حدث خطأ أثناء إرسال الأمر.'], 500);
        }
    }

    // Send R0 unlock command (to generate operation key)
    protected function sendR0UnlockCommand($imei)
    {
        $key = 20;  // Example key value
        $userId = 1234;  // Example user ID
        $timestamp = time();  // Current Unix timestamp

        // Format the R0 unlock command to get the operation KEY
        $command = "*SCOS,OM,{$imei},R0,0,{$key},{$userId},{$timestamp}#\n";

        // Send the R0 command to the scooter to get the operation KEY
        fwrite($this->conn, $command);
        Log::info("🚀 تم إرسال الأمر R0: $command");

        // Now send the L0 unlock command (using the key from the R0 command)
        $this->sendL0UnlockCommand($imei, $key, $userId, $timestamp);
    }

    // Send L0 unlock command to unlock the scooter
    protected function sendL0UnlockCommand($imei, $key, $userId, $timestamp)
    {
        // Format the L0 unlock command with the KEY
        $command = "*SCOS,OM,{$imei},L0,{$key},{$userId},{$timestamp}#\n";

        // Send the L0 unlock command to unlock the scooter
        fwrite($this->conn, $command);
        Log::info("🚀 تم إرسال الأمر L0: $command");
    }
}
