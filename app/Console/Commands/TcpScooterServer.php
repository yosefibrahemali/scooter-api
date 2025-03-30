<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Socket\ConnectionInterface;
use Ratchet\Client\connect;

class TcpScooterServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start TCP server for scooter communication';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $loop = Loop::get();
        $server = new SocketServer('0.0.0.0:3000', [], $loop);

        $centerLat = 32.3718003;
        $centerLon = 15.0909694;
        $geofenceRadius = 1000;

        $server->on('connection', function (ConnectionInterface $socket) use ($loop, $centerLat, $centerLon, $geofenceRadius) {
            echo "Device connected\n";

            // إرسال أوامر إلى السكوتر عند الاتصال
            $socket->write("*SCOS,OM,868351077123154,S6#\n");
            $socket->write("*SCOS,OM,868351077123154,R0,2411," . time() . "#\n");
            $socket->write("*SCOS,OM,868351077123154,L1,2411#\n");

            // استقبال البيانات القادمة من السكوتر
            $socket->on('data', function ($data) use ($socket, $centerLat, $centerLon, $geofenceRadius) {
                $databreak = explode(",", trim($data));

                if (isset($databreak[3])) {
                    if ($databreak[3] === "D0") {
                        // تحويل إحداثيات الموقع
                        $latitude = $this->convertToDecimalDegrees($databreak[7]);
                        $longitude = $this->convertToDecimalDegrees($databreak[9]);

                        echo "🚲 الموقع الحالي للسكوتر: LAT = $latitude, LNG = $longitude\n";

                        // إرسال إشعار إلى WebSockets
                        $this->sendWebSocketMessage('warmessage', "الرجاء إعادة الدراجة إلى المنطقة المحددة");
                        $this->sendWebSocketMessage('message', $databreak);
                    } elseif ($databreak[3] === "S6") {
                        $this->sendWebSocketMessage('infomessage', $databreak);
                    }
                }
            });

            $socket->on('end', function () {
                echo "Device disconnected\n";
            });

            $socket->on('error', function ($err) {
                echo "Error: {$err->getMessage()}\n";
            });
        });

        echo "✅ TCP Server running on port 3000...\n";
        $loop->run();
    }

    private function convertToDecimalDegrees($value)
    {
        return floatval($value) / 1000000;
    }

    private function sendWebSocketMessage($event, $message)
    {
        connect('ws://127.0.0.1:6001')->then(function ($conn) use ($event, $message) {
            $conn->send(json_encode(['event' => $event, 'data' => $message]));
            $conn->close();
        }, function ($e) {
            echo "Could not connect to WebSocket server: {$e->getMessage()}\n";
        });
    }
    
}
