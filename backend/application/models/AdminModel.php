<?php
/**
 * 管理员 Model（lc_admin）
 */
class AdminModel extends BaseModel
{
    protected string $table = 'lc_admin';

    /** 根据登录账号查找管理员 */
    public function findByUsername(string $username): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE username = ? AND is_delete = 0 LIMIT 1",
            [$username]
        );
    }

    /** 根据 ID 查找管理员 */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE id = ? AND is_delete = 0 LIMIT 1",
            [$id]
        );
    }

    /** 更新最后登录时间和 IP */
    public function updateLoginInfo(int $id): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->db->execute(
            "UPDATE `{$this->table}`
             SET last_login_at = NOW(), last_login_ip = ?, update_time = NOW()
             WHERE id = ?",
            [$ip, $id]
        );
    }
}
