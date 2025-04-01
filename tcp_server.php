<?php

// Define the server IP and port
$serverIp = '0.0.0.0'; // Listen on all interfaces
$serverPort = 12345;   // Replace with the port your scooter connects to

// Create a TCP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    die("Failed to create socket: " . socket_strerror(socket_last_error()));
}

// Bind the socket to the IP and port
if (!socket_bind($socket, $serverIp, $serverPort)) {
    die("Failed to bind socket: " . socket_strerror(socket_last_error()));
}

// Start listening for incoming connections
if (!socket_listen($socket)) {
    die("Failed to listen on socket: " . socket_strerror(socket_last_error()));
}

echo "TCP server is running on $serverIp:$serverPort\n";

// Accept incoming connections
while (true) {
    $clientSocket = socket_accept($socket);
    if ($clientSocket === false) {
        echo "Failed to accept client connection: " . socket_strerror(socket_last_error()) . "\n";
        continue;
    }

    // Read data from the scooter
    $data = '';
    while ($buffer = socket_read($clientSocket, 1024)) {
        $data .= $buffer;
        if (strpos($data, "\n") !== false) { // End of command
            break;
        }
    }

    echo "Received data: $data\n";

    // Parse and process the command
    $response = processCommand($data);

    // Send a response back to the scooter
    socket_write($clientSocket, $response, strlen($response));

    // Close the client socket
    socket_close($clientSocket);
}

// Close the server socket
socket_close($socket);

/**
 * Process incoming commands from the scooter
 */
function processCommand($command) {
    // Example: Check if the command is a heartbeat (H0)
    if (strpos($command, 'H0') !== false) {
        return "*SCOS,OM,123456789123456,H0#<LF>\n"; // Example response
    }

    // Handle other commands as per the protocol
    return "*SCOS,OM,123456789123456,INVALID#<LF>\n";
}