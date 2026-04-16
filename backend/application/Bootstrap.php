<?php
/**
 * Yaf Bootstrap
 * 按方法名前缀 _init 顺序自动调用
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    /** 1. 注册全局配置 */
    public function _initConfig(): void
    {
        $config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $config);
    }

    /** 2. 初始化 PDO 数据库连接 */
    public function _initDatabase(): void
    {
        $config = Yaf_Registry::get('config');
        Db::getInstance($config->database->toArray());
    }

    /** 3. 注册 JWT 鉴权插件 */
    public function _initPlugin(Yaf_Dispatcher $dispatcher): void
    {
        $dispatcher->registerPlugin(new AuthPlugin());
    }

    /** 4. 跨域响应头（OPTIONS 预检直接返回 204） */
    public function _initCors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Token');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /** 5. 纯 API 模式，禁用视图渲染 */
    public function _initView(Yaf_Dispatcher $dispatcher): void
    {
        $dispatcher->disableView();
    }
}
