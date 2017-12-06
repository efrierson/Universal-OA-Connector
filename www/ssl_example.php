<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 7486
$connection = ssh2_connect('demoapp.carlconnect.com', 22);
ssh2_auth_pubkey_file($connection, 'ebscosip', '../keys/tlc_ebsco.pub', '../keys/tlc_ebsco');

$tunnel_host = 'demoapp.carlconnect.com';
$tunnel_port = 6001;

$tunnel = ssh2_tunnel($connection, $tunnel_host, $tunnel_port);


/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

/* check for actual truly false result using ===*/
if ($socket === false) {
    echo "SIP2: socket_create() failed: reason: " . socket_strerror($socket);
    return false;
} else {
    echo "SIP2: Socket Created"; 
}

$address = $tunnel_host;
$port = $tunnel_port;

echo "SIP2: Attempting to connect to '$address' on port '$port'..."; 

/* open a connection to the host */
$result = socket_connect($socket, $address, $port);
if (!$result) {
    echo "SIP2: socket_connect() failed.\nReason: ($result) " . socket_strerror($result);
} else {
    echo "SIP2: --- SOCKET READY ---";
}

socket_close($socket);

?>

