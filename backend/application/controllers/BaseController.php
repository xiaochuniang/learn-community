<?php
/**
 * BaseController
 *
 * 所有 Controller 的基类，提供：
 *   - json() / success() / fail()   统一响应输出
 *   - input() / all()               参数读取（GET + POST + JSON body）
 *   - page()                        分页参数
 *   - getAdminId()                  获取当前登录管理员 ID
 */
abstract class BaseController extends Yaf_Controller_Abstract
{
    // ── 响应 ─────────────────────────────────────────────────────────

    /**
     * 输出 JSON 并终止脚本
     *
     * @param int    $code    业务返回码
     * @param string $message 提示文字
     * @param mixed  $data    业务数据
     */
    protected function json(int $code, string $message, mixed $data = null): never
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['code' => $code, 'message' => $message, 'data' => $data],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    protected function success(mixed $data = null, string $message = '操作成功'): never
    {
        $this->json(Response::OK, $message, $data);
    }

    protected function fail(string $message = '操作失败', int $code = Response::BAD_REQUEST): never
    {
        $this->json($code, $message, null);
    }

    // ── 参数读取 ─────────────────────────────────────────────────────

    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    protected function all(): array
    {
        static $parsed = null;
        if ($parsed === null) {
            $body   = (string)file_get_contents('php://input');
            $json   = ($body !== '') ? (json_decode($body, true) ?? []) : [];
            $parsed = array_merge($_GET, $_POST, $json);
        }
        return $parsed;
    }

    /**
     * 提取分页参数
     * @return array{page: int, limit: int, offset: int}
     */
    protected function page(): array
    {
        $page  = min(10000, max(1, (int)$this->input('page', 1)));
        $limit = min(100, max(1, (int)$this->input('limit', 20)));
        return ['page' => $page, 'limit' => $limit, 'offset' => ($page - 1) * $limit];
    }

    // ── 身份 ─────────────────────────────────────────────────────────

    protected function getAdminId(): int
    {
        return (int)(Yaf_Registry::get('admin_id') ?? 0);
    }

    protected function getAdminRole(): int
    {
        return (int)(Yaf_Registry::get('admin_role') ?? 0);
    }
}
