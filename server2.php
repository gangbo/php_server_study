<?php
/**
 * Created by daigangbo.
 * User: daigangbo daigangbo@gmail.com
 * Date: 2020/7/24
 * Time: 10:30
 */

$server = stream_socket_server('tcp://0.0.0.0:8899', $errNo, $errStr) or die("create server die!!");


while (1) {
    $conn = @stream_socket_accept($server, 10.0);
    if (!$conn) {
        echo ">>>>>>>>>>>>next\n";
        continue;
    }
    buz($conn);

}

function buz($conn)
{
    require_once 'Foo.php';
    $content = Foo::hello() . "\n";
    $content_len = strlen($content);
    $response = "HTTP/1.1 200 OK\r\n"
        . "Content-Type: text/html; charset=utf-8\r\n"
        . "Connection: keep-alive\r\n"
        . "Content-Length: $content_len\r\n";
    $response .= "\r\n" . $content;
    $request = fread($conn, 1024);
    fwrite($conn, $response);
    fclose($conn);

}
