<?php

$host = "0.0.0.0";
$port = 5000;

$socket = stream_socket_server("tcp://$host:$port", $errno, $errstr);

if (!$socket) {
    die("❌ فشل تشغيل السيرفر: $errstr ($errno)\n");
}

echo "🔵 خادم TCP يعمل على $host:$port...\n";

while ($conn = @stream_socket_accept($socket, 10)) {
    fwrite($conn, "✅ مرحبًا، تم الاتصال بنجاح!\n");
    fclose($conn);
}

fclose($socket);
