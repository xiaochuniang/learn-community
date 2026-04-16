<?php
/**
 * 业务返回码常量
 */
class Response
{
    const OK           = 0;    // 操作成功
    const BAD_REQUEST  = 400;  // 参数错误
    const UNAUTHORIZED = 401;  // 未登录 / Token 失效
    const FORBIDDEN    = 403;  // 无操作权限
    const NOT_FOUND    = 404;  // 资源不存在
    const SERVER_ERROR = 500;  // 服务器内部错误
}
