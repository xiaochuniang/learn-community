<?php
/**
 * 用户 Model（lc_user）
 */
class UserModel extends BaseModel
{
    protected string $table = 'lc_user';

    /**
     * 分页查询用户列表
     *
     * @param int    $page     当前页
     * @param int    $limit    每页条数
     * @param string $keyword  搜索关键词（昵称 / 真实姓名 / 手机号）
     * @param mixed  $status   状态过滤：'' 不过滤，0 禁用，1 正常
     * @param mixed  $userType 用户类型：'' 不过滤，1 学生，2 家长
     */
    public function paginate(int $page, int $limit, string $keyword, mixed $status, mixed $userType): array
    {
        $offset = ($page - 1) * $limit;
        $where  = ['is_delete = 0'];
        $params = [];

        if ($keyword !== '') {
            $like     = "%{$keyword}%";
            $where[]  = '(nickname LIKE ? OR real_name LIKE ? OR mobile LIKE ?)';
            $params   = array_merge($params, [$like, $like, $like]);
        }
        if ($status !== '' && $status !== null) {
            $where[]  = 'status = ?';
            $params[] = (int)$status;
        }
        if ($userType !== '' && $userType !== null) {
            $where[]  = 'user_type = ?';
            $params[] = (int)$userType;
        }

        $cond  = implode(' AND ', $where);
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE {$cond}",
            $params
        );
        $list  = $this->db->fetchAll(
            "SELECT id, nickname, real_name, mobile, avatar, gender, user_type, status,
                    total_study_min, balance, last_login_at, create_time
             FROM `{$this->table}`
             WHERE {$cond}
             ORDER BY id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->buildPageResult($page, $limit, $list, $total);
    }

    /**
     * 用户详情（脱敏：不含敏感字段）
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT id, openid, nickname, real_name, mobile, avatar, gender, birthday,
                    school, grade, user_type, status, balance, total_study_min,
                    last_login_at, last_login_ip, register_source, create_time, update_time
             FROM `{$this->table}`
             WHERE id = ? AND is_delete = 0
             LIMIT 1",
            [$id]
        );
    }

    /** 修改用户状态（启用/禁用） */
    public function changeStatus(int $id, int $status): void
    {
        $this->db->execute(
            "UPDATE `{$this->table}` SET status = ?, update_time = NOW() WHERE id = ? AND is_delete = 0",
            [$status, $id]
        );
    }
}
