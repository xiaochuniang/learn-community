<?php
/**
 * 陪伴师 Model（lc_tutor + lc_tutor_audit）
 *
 * 重点：
 *   - processAudit()    通过事务同时更新审核表和陪伴师主表
 *   - findDetailById()  附带证件文件列表和最新审核记录
 */
class TutorModel extends BaseModel
{
    protected string $table      = 'lc_tutor';
    protected string $auditTable = 'lc_tutor_audit';

    // ── 陪伴师列表 ───────────────────────────────────────────────────

    /**
     * 分页查询陪伴师列表（JOIN lc_user 补充用户基础信息）
     *
     * @param int    $page        当前页
     * @param int    $limit       每页条数
     * @param mixed  $auditStatus 审核状态：'' 不过滤，0 待审核，1 通过，2 拒绝
     * @param string $keyword     关键词（真实姓名 / 手机号）
     */
    public function paginate(int $page, int $limit, mixed $auditStatus, string $keyword): array
    {
        $offset = ($page - 1) * $limit;
        $where  = ['t.is_delete = 0'];
        $params = [];

        if ($auditStatus !== '' && $auditStatus !== null) {
            $where[]  = 't.audit_status = ?';
            $params[] = (int)$auditStatus;
        }
        if ($keyword !== '') {
            $like     = "%{$keyword}%";
            $where[]  = '(t.real_name LIKE ? OR t.mobile LIKE ?)';
            $params   = array_merge($params, [$like, $like]);
        }

        $cond  = implode(' AND ', $where);
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` t WHERE {$cond}",
            $params
        );
        $list  = $this->db->fetchAll(
            "SELECT t.id, t.user_id, t.real_name, t.mobile, t.avatar, t.gender,
                    t.education, t.school, t.service_price, t.audit_status,
                    t.audit_remark, t.audit_at, t.status, t.score, t.order_count,
                    t.total_income, t.create_time,
                    u.nickname, u.openid
             FROM `{$this->table}` t
             LEFT JOIN lc_user u ON u.id = t.user_id
             WHERE {$cond}
             ORDER BY t.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->buildPageResult($page, $limit, $list, $total);
    }

    /**
     * 陪伴师详情（含证件文件 + 最新审核记录）
     */
    public function findDetailById(int $id): ?array
    {
        $tutor = $this->db->fetchOne(
            "SELECT t.*, u.nickname, u.openid, u.gender AS user_gender
             FROM `{$this->table}` t
             LEFT JOIN lc_user u ON u.id = t.user_id
             WHERE t.id = ? AND t.is_delete = 0
             LIMIT 1",
            [$id]
        );
        if ($tutor === null) {
            return null;
        }

        // 附带证件文件列表
        $tutor['cert_files'] = $this->db->fetchAll(
            "SELECT id, file_type, file_url, file_name, mime_type, create_time
             FROM lc_cert_file
             WHERE tutor_id = ? AND status = 1 AND is_delete = 0
             ORDER BY file_type ASC, id ASC",
            [$id]
        );

        // 最新一条审核记录
        $tutor['latest_audit'] = $this->db->fetchOne(
            "SELECT id, audit_type, status, remark, admin_id, audit_at, create_time
             FROM `{$this->auditTable}`
             WHERE tutor_id = ? AND is_delete = 0
             ORDER BY id DESC
             LIMIT 1",
            [$id]
        );

        return $tutor;
    }

    // ── 审核列表 ─────────────────────────────────────────────────────

    /**
     * 审核申请分页列表（JOIN 陪伴师基础信息）
     *
     * @param int    $page   当前页
     * @param int    $limit  每页条数
     * @param string $status 审核状态：'' 不过滤，0 待审核，1 通过，2 拒绝
     */
    public function getAuditList(int $page, int $limit, string $status): array
    {
        $offset = ($page - 1) * $limit;
        $where  = ['a.is_delete = 0'];
        $params = [];

        if ($status !== '') {
            $where[]  = 'a.status = ?';
            $params[] = (int)$status;
        }

        $cond  = implode(' AND ', $where);
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->auditTable}` a WHERE {$cond}",
            $params
        );
        $list  = $this->db->fetchAll(
            "SELECT a.id, a.tutor_id, a.audit_type, a.status, a.remark,
                    a.admin_id, a.audit_at, a.create_time,
                    t.real_name, t.mobile, t.avatar, t.education, t.school,
                    u.nickname AS user_nickname
             FROM `{$this->auditTable}` a
             LEFT JOIN `{$this->table}` t ON t.id = a.tutor_id
             LEFT JOIN lc_user u          ON u.id = t.user_id
             WHERE {$cond}
             ORDER BY a.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->buildPageResult($page, $limit, $list, $total);
    }

    /** 根据 ID 查找审核记录（简版，用于校验） */
    public function findAuditById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->auditTable}` WHERE id = ? AND is_delete = 0 LIMIT 1",
            [$id]
        );
    }

    /**
     * 审核申请详情（含 submit_data JSON 反序列化）
     */
    public function findAuditDetail(int $id): ?array
    {
        $audit = $this->db->fetchOne(
            "SELECT a.*,
                    t.real_name, t.mobile, t.avatar, t.school, t.education,
                    u.nickname AS user_nickname
             FROM `{$this->auditTable}` a
             LEFT JOIN `{$this->table}` t ON t.id = a.tutor_id
             LEFT JOIN lc_user u          ON u.id = t.user_id
             WHERE a.id = ? AND a.is_delete = 0
             LIMIT 1",
            [$id]
        );
        if ($audit === null) {
            return null;
        }

        // submit_data 是 MySQL JSON 字段，PDO 取出为字符串，手动解码
        if (!empty($audit['submit_data']) && is_string($audit['submit_data'])) {
            $audit['submit_data'] = json_decode($audit['submit_data'], true);
        }

        return $audit;
    }

    // ── 审核操作（事务） ────────────────────────────────────────────

    /**
     * 处理审核：
     *   1. 更新 lc_tutor_audit.status
     *   2. 更新 lc_tutor.audit_status
     *   3. 审核通过时，自动将陪伴师上线（status=1）
     *
     * 全程使用事务，任何步骤失败自动回滚。
     *
     * @param int    $auditId  审核记录 ID
     * @param int    $tutorId  陪伴师 ID
     * @param int    $status   1 通过，2 拒绝
     * @param string $remark   审核意见
     * @param int    $adminId  操作管理员 ID
     */
    public function processAudit(
        int    $auditId,
        int    $tutorId,
        int    $status,
        string $remark,
        int    $adminId
    ): void {
        $pdo = $this->db->getPdo();
        if ($pdo->inTransaction()) {
            throw new RuntimeException('嵌套事务不被支持');
        }
        $pdo->beginTransaction();

        try {
            // 1. 更新审核记录
            $this->db->execute(
                "UPDATE `{$this->auditTable}`
                 SET status = ?, remark = ?, admin_id = ?, audit_at = NOW(), update_time = NOW()
                 WHERE id = ?",
                [$status, $remark, $adminId, $auditId]
            );

            // 2. 更新陪伴师主表的审核状态
            $this->db->execute(
                "UPDATE `{$this->table}`
                 SET audit_status = ?, audit_remark = ?, audit_at = NOW(),
                     audit_admin_id = ?, update_time = NOW()
                 WHERE id = ?",
                [$status, $remark, $adminId, $tutorId]
            );

            // 3. 审核通过 → 自动上线
            if ($status === 1) {
                $this->db->execute(
                    "UPDATE `{$this->table}` SET status = 1, update_time = NOW() WHERE id = ?",
                    [$tutorId]
                );
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** 修改陪伴师上线/下线状态 */
    public function changeStatus(int $tutorId, int $status): void
    {
        $this->db->execute(
            "UPDATE `{$this->table}` SET status = ?, update_time = NOW()
             WHERE id = ? AND is_delete = 0",
            [$status, $tutorId]
        );
    }
}
