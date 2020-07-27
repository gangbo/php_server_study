<?php
/**
 * Created by daigangbo.
 * User: daigangbo daigangbo@gmail.com
 * Date: 2020/7/24
 * Time: 10:30
 */


$childs = [];
for ($i = 0; $i < 1; $i++) {

    $pid = pcntl_fork();
    if (!$pid) {
        echo "执行子进程 !!!!" . PHP_EOL;
        buz();

    } else {
        $childs[] = $pid;
        echo "获取子进程pid:$pid !!!!" . PHP_EOL;
    }
}
function buz()
{
    $context = stream_context_create([
        'socket' => [
            'so_reuseport' => true,
        ]
    ]);
    $server = stream_socket_server('tcp://0.0.0.0:8899', $errNo, $errStr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
        $context) or die("create server die!!");
    require_once "Foo.php";
    $content = Foo::hello() . "\n";
    $content_len = strlen($content);
    $response = "HTTP/1.1 200 OK\r\n"
        . "Content-Type: text/html; charset=utf-8\r\n"
        . "Connection: keep-alive\r\n"
        . "Content-Length: $content_len\r\n";
    $response .= "\r\n" . $content;

    while (1) {
        $conn = @stream_socket_accept($server, 10.0);
        if (!$conn) {
            echo ">>>>>>>>>>>>next\n";
            continue;
        }

        $request = fread($conn, 1024);
        fwrite($conn, $response);
        fclose($conn);
    }

}


while (count($childs) > 0) {
    echo "检查子进程状态\n";
    foreach ($childs as $key => $pid) {
        $res = pcntl_waitpid($pid, $status, WNOHANG);
        // If the process has already exited
        if ($res == -1 || $res > 0) {
            echo "子进程 $pid 退出\n";
            unset($childs[$key]);
        }
    }
    sleep(1);
}
