<?php

namespace App\Services;

class ScooterUnlockService
{
    protected $host = '138.199.198.151'; // عنوان IP للسكوتر
    protected $port = 3000; // تغيير المنفذ إلى 3000

    public function unlockScooter($imei)
    {
        $command = "*SCOS,OM,{$imei},R0,0,20,1234," . time() . "#\n";

        $socket = @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, 10);

        if (!$socket) {
            return "خطأ في الاتصال: $errstr ($errno)";
        }

        fwrite($socket, $command);
        $response = fread($socket, 1024);
        fclose($socket);

        return $response ?: "تم إرسال الأمر بنجاح!";
    }
}