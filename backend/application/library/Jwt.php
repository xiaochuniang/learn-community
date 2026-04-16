<?php
/**
 * 轻量 JWT 实现（HS256）
 * 无需第三方扩展，仅依赖 hash_hmac / base64_encode
 */
class Jwt
{
    private static string $secret = '';

    public static function init(string $secret): void
    {
        self::$secret = $secret;
    }

    /**
     * 生成 Token
     *
     * @param array $payload 自定义载荷（不要存敏感数据）
     * @param int   $ttl     有效期（秒），默认 86400
     */
    public static function encode(array $payload, int $ttl = 86400): string
    {
        $header  = self::b64e(json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_UNESCAPED_UNICODE));
        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;
        $body    = self::b64e(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $sig     = self::b64e(hash_hmac('sha256', "{$header}.{$body}", self::$secret, true));
        return "{$header}.{$body}.{$sig}";
    }

    /**
     * 验证并解析 Token
     *
     * @return array|null  成功返回 payload，签名错误/过期返回 null
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header, $body, $sig] = $parts;
        $expected = self::b64e(hash_hmac('sha256', "{$header}.{$body}", self::$secret, true));
        if (!hash_equals($expected, $sig)) {
            return null;
        }
        $payload = json_decode(self::b64d($body), true);
        if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            return null;
        }
        return $payload;
    }

    // ── 内部辅助 ─────────────────────────────────────────────────────

    private static function b64e(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string
    {
        $pad  = (4 - strlen($data) % 4) % 4;
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', $pad));
    }
}
