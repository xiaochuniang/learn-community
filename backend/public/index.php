<?php
/**
 * 入口文件
 * 访问地址示例：http://api.yourdomain.com/auth/login
 */

define('APP_PATH',  realpath(dirname(__FILE__) . '/../'));
define('ROOT_PATH', APP_PATH);
define('APP_DEBUG', false);   // 生产环境改为 false

// 全局异常兜底（Yaf 未捕获时）
set_exception_handler(static function (Throwable $e): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code'    => 500,
        'message' => APP_DEBUG ? $e->getMessage() : '服务器内部错误',
        'data'    => APP_DEBUG ? ['file' => $e->getFile(), 'line' => $e->getLine()] : null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

$app = new Yaf_Application(APP_PATH . '/conf/application.ini');
$app->bootstrap()->run();
