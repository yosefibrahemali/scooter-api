<?php

namespace App\Http\Controllers;

use App\Services\TcpServerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class ScooterController extends Controller
{
    public function unlock(Request $request)
    {
        $server = TcpServerService::getInstance();
        
        if (!$server->isRunning()) {
            return response()->json([
                'success' => false,
                'message' => 'TCP server not running',
                'solution' => 'Run the server first with: php artisan tcp:server 5000'
            ], 503);
        }

        $scooterId = $request->input('scooter_id');
        $connectedScooters = $server->getConnectedScooters();

        if (!in_array($scooterId, $connectedScooters)) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not connected',
                'connected_scooters' => $connectedScooters,
                'solution' => 'Ensure scooter is powered on and connected to network'
            ], 404);
        }

        $command = $this->buildCommand($scooterId, $request->input('user_id'));
        
        if ($server->sendCommandToScooter($scooterId, $command)) {
            return response()->json([
                'success' => true,
                'message' => 'Command sent successfully',
                'command' => $command
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send command',
            'command' => $command
        ], 500);
    }

    public function unlockScooter($imei, $userId)
    {
        $host = '41.254.70.174';
        $port = 24109;

        $timestamp = time();
        $keyTime = 20; // مدة صلاحية المفتاح

        // 1. الاتصال بالسيرفر
        $socket = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 10);

        if (!$socket) {
            return "فشل في الاتصال: $errstr ($errno)";
        }

        // 2. إرسال أمر R0 لطلب المفتاح
        $r0Command = "*SCOS,OM,{$imei},R0,0,{$keyTime},{$userId},{$timestamp}#\n";
        fwrite($socket, $r0Command);

        // 3. قراءة الرد للحصول على المفتاح
        $response = fread($socket, 1024);

        if (!$response || !str_contains($response, '*SCOR')) {
            fclose($socket);
            return "فشل في استقبال رد R0";
        }

        // 4. استخراج المفتاح من الرد
        // مثال الرد: *SCOR,OM,123456789123456,R0,55,1234,1497689816#
        preg_match('/\*SCOR,OM,'.$imei.',R0,(\d+),'.$userId.','.$timestamp.'#/', $response, $matches);
        
        if (!isset($matches[1])) {
            fclose($socket);
            return "لم يتم العثور على المفتاح في الرد";
        }

        $operationKey = $matches[1];

        // 5. إرسال أمر L0 لفتح القفل باستخدام المفتاح
        $l0Command = "*SCOS,OM,{$imei},L0,{$operationKey},{$userId},{$timestamp}#\n";
        fwrite($socket, $l0Command);

        // 6. استقبال الرد النهائي من أمر L0
        $finalResponse = fread($socket, 1024);

        fclose($socket);

        return [
            'r0_sent' => $r0Command,
            'r0_response' => $response,
            'operation_key' => $operationKey,
            'l0_sent' => $l0Command,
            'l0_response' => $finalResponse,
        ];
    }


    // ... (بقية الدوال)
}
