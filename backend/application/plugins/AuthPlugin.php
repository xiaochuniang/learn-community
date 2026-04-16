<?php
/**
 * JWT 鉴权插件
 *
 * 所有接口默认需要携带有效 Token（Authorization: Bearer <token>）
 * 白名单路由无需鉴权（controller::action 小写）
 */
class AuthPlugin extends Yaf_Plugin_Abstract
{
    /** 不需要鉴权的路由（controller::action 均小写） */
    private const WHITELIST = [
        'auth::login',
    ];

    public function routerShutdown(
        Yaf_Request_Abstract  $request,
        Yaf_Response_Abstract $response
    ): void {
        $controller = strtolower($request->getControllerName());
        $action     = strtolower($request->getActionName());
        $route      = "{$controller}::{$action}";

        if (in_array($route, self::WHITELIST, true)) {
            return;
        }

        $token = $this->extractToken();
        if ($token === '') {
            $this->abort(Response::UNAUTHORIZED, '请先登录');
        }

        $config = Yaf_Registry::get('config');
        Jwt::init($config->jwt->secret);
        $payload = Jwt::decode($token);

        if ($payload === null) {
            $this->abort(Response::UNAUTHORIZED, 'Token 已过期或无效，请重新登录');
        }

        // 将身份信息存入 Registry，供 Controller 使用
        Yaf_Registry::set('admin_id',   (int)($payload['admin_id'] ?? 0));
        Yaf_Registry::set('admin_role', (int)($payload['role_id']  ?? 0));
    }

    // ── 私有辅助 ─────────────────────────────────────────────────────

    private function extractToken(): string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_X_TOKEN'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function abort(int $code, string $message): never
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['code' => $code, 'message' => $message, 'data' => null],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}
