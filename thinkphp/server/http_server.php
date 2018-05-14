<?php

$http_server = new swoole_http_server('0.0.0.0', 8815);
$http_server->set([
        'enable_static_handler' => true,
        'document_root' => '/usr/local/nginx/html/swoole/thinkphp/public/static',
        'worker_num' => 5
    ]
);

    $http_server->on('WorkerStart', function(swoole_server $server, $worker_id) {
        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../application/');

        // ThinkPHP 引导文件
        // 1. 加载基础文件
        require __DIR__ . '/../thinkphp/base.php';
    });
    $http_server->on('request', function (swoole_http_request $request, swoole_http_response $response) use ($http_server) {

        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }

        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        ob_start();
        try {
            // 2. 执行应用并响应
            think\App::run()->send();
        } catch (\Exception $exception) {
            // todo
        }
        echo '--Action--' . request()->action() . PHP_EOL;
        $result = ob_get_contents();
        ob_end_clean();
        $response->end($result);
        $http_server->close();
});

$http_server->start();