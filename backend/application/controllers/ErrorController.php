<?php
/**
 * 全局异常/错误处理控制器
 *
 * Yaf 在 catchException=TRUE 模式下，若产生异常会自动路由到此控制器的 errorAction。
 */
class ErrorController extends BaseController
{
    public function errorAction(): never
    {
        /** @var Throwable|null $e */
        $e = $this->getRequest()->getException();

        if ($e instanceof Throwable) {
            $message = $e->getMessage() ?: '服务器内部错误';
            $data    = APP_DEBUG
                ? ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]
                : null;
            $this->json(Response::SERVER_ERROR, $message, $data);
        }

        $this->json(Response::SERVER_ERROR, '服务器内部错误');
    }
}
