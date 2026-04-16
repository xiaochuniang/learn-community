<?php
/**
 * 登录鉴权接口
 *
 * POST  /auth/login    管理员登录，返回 JWT Token
 * GET   /auth/info     获取当前登录管理员信息
 * POST  /auth/logout   退出登录（Token 无状态，客户端丢弃即可）
 */
class AuthController extends BaseController
{
    /**
     * POST /auth/login
     *
     * Request  { "username": "admin", "password": "123456" }
     * Response { token, expires, admin: { id, username, real_name, avatar, role_id } }
     */
    public function loginAction(): never
    {
        $data = $this->all();

        $v = new Validator($data);
        $v->required('username', '请输入登录账号')
          ->required('password', '请输入登录密码')
          ->minLen('password', 6, '密码不能少于6位');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $model = new AdminModel();
        $admin = $model->findByUsername((string)$data['username']);

        if ($admin === null) {
            $this->fail('账号不存在');
        }
        if ((int)$admin['status'] !== 1) {
            $this->fail('账号已被禁用，请联系超级管理员');
        }
        if (!password_verify((string)$data['password'], $admin['password'])) {
            $this->fail('密码错误');
        }

        // 记录最后登录时间/IP
        $model->updateLoginInfo((int)$admin['id']);

        // 签发 JWT
        $config = Yaf_Registry::get('config');
        Jwt::init($config->jwt->secret);
        $ttl   = (int)$config->jwt->expire;
        $token = Jwt::encode([
            'admin_id'  => (int)$admin['id'],
            'username'  => $admin['username'],
            'role_id'   => (int)$admin['role_id'],
        ], $ttl);

        $this->success([
            'token'   => $token,
            'expires' => time() + $ttl,
            'admin'   => [
                'id'        => (int)$admin['id'],
                'username'  => $admin['username'],
                'real_name' => $admin['real_name'],
                'avatar'    => $admin['avatar'],
                'role_id'   => (int)$admin['role_id'],
            ],
        ], '登录成功');
    }

    /**
     * GET /auth/info
     * 获取当前登录管理员的基本信息（依赖 Token）
     */
    public function infoAction(): never
    {
        $adminId = $this->getAdminId();
        $model   = new AdminModel();
        $admin   = $model->findById($adminId);

        if ($admin === null) {
            $this->fail('管理员不存在', Response::UNAUTHORIZED);
        }

        unset($admin['password']);   // 绝对不返回密码哈希
        $this->success($admin);
    }

    /**
     * POST /auth/logout
     * JWT 无状态；前端清除本地 Token 即完成退出。
     * 此接口用于前端统一调用，方便后续扩展黑名单机制。
     */
    public function logoutAction(): never
    {
        $this->success(null, '已退出登录');
    }
}
