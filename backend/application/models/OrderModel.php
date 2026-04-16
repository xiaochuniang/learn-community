<?php
/**
 * 订单 Model（lc_order）
 *
 * 重点：
 *   - refund()    通过事务同步更新订单状态 + 佣金冻结
 *   - getStats()  各状态订单数量 + 总成交额
 */
class OrderModel extends BaseModel
{
    protected string $table = 'lc_order';

    // ── 列表 ─────────────────────────────────────────────────────────

    /**
     * 分页查询订单列表（多维度过滤）
     *
     * @param int   $page
     * @param int   $limit
     * @param array $filters  {order_status, pay_status, keyword, date_start, date_end}
     */
    public function paginate(int $page, int $limit, array $filters): array
    {
        $offset = ($page - 1) * $limit;
        $where  = ['o.is_delete = 0'];
        $params = [];

        if ((string)$filters['order_status'] !== '') {
            $where[]  = 'o.order_status = ?';
            $params[] = (int)$filters['order_status'];
        }
        if ((string)$filters['pay_status'] !== '') {
            $where[]  = 'o.pay_status = ?';
            $params[] = (int)$filters['pay_status'];
        }
        if ($filters['keyword'] !== '') {
            $like     = "%{$filters['keyword']}%";
            $where[]  = '(o.order_no LIKE ? OR u.mobile LIKE ? OR u.nickname LIKE ?)';
            $params   = array_merge($params, [$like, $like, $like]);
        }
        if ($filters['date_start'] !== '') {
            $where[]  = 'DATE(o.create_time) >= ?';
            $params[] = $filters['date_start'];
        }
        if ($filters['date_end'] !== '') {
            $where[]  = 'DATE(o.create_time) <= ?';
            $params[] = $filters['date_end'];
        }

        $cond  = implode(' AND ', $where);
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*)
             FROM `{$this->table}` o
             LEFT JOIN lc_user u ON u.id = o.user_id
             WHERE {$cond}",
            $params
        );
        $list  = $this->db->fetchAll(
            "SELECT o.id, o.order_no, o.user_id, o.tutor_id, o.service_type,
                    o.service_date, o.service_minutes, o.amount, o.pay_amount,
                    o.pay_type, o.pay_status, o.order_status, o.create_time,
                    u.nickname  AS user_nickname,
                    u.mobile    AS user_mobile,
                    t.real_name AS tutor_name
             FROM `{$this->table}` o
             LEFT JOIN lc_user  u ON u.id = o.user_id
             LEFT JOIN lc_tutor t ON t.id = o.tutor_id
             WHERE {$cond}
             ORDER BY o.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->buildPageResult($page, $limit, $list, $total);
    }

    /** 查询指定用户的订单（用于 /user/orders 接口） */
    public function paginateByUser(int $userId, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        $total  = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE user_id = ? AND is_delete = 0",
            [$userId]
        );
        $list   = $this->db->fetchAll(
            "SELECT o.id, o.order_no, o.tutor_id, o.service_type,
                    o.service_date, o.service_minutes, o.amount, o.pay_amount,
                    o.pay_status, o.order_status, o.create_time,
                    t.real_name AS tutor_name, t.avatar AS tutor_avatar
             FROM `{$this->table}` o
             LEFT JOIN lc_tutor t ON t.id = o.tutor_id
             WHERE o.user_id = ? AND o.is_delete = 0
             ORDER BY o.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            [$userId]
        );
        return $this->buildPageResult($page, $limit, $list, $total);
    }

    // ── 单条查询 ─────────────────────────────────────────────────────

    /** 查询原始订单行（用于状态校验） */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE id = ? AND is_delete = 0 LIMIT 1",
            [$id]
        );
    }

    /**
     * 订单详情（JOIN 用户 + 陪伴师 + 评价）
     */
    public function findDetailById(int $id): ?array
    {
        $order = $this->db->fetchOne(
            "SELECT o.*,
                    u.nickname   AS user_nickname,
                    u.mobile     AS user_mobile,
                    u.avatar     AS user_avatar,
                    t.real_name  AS tutor_name,
                    t.mobile     AS tutor_mobile,
                    t.avatar     AS tutor_avatar
             FROM `{$this->table}` o
             LEFT JOIN lc_user  u ON u.id = o.user_id
             LEFT JOIN lc_tutor t ON t.id = o.tutor_id
             WHERE o.id = ? AND o.is_delete = 0
             LIMIT 1",
            [$id]
        );
        if ($order === null) {
            return null;
        }

        // 附带评价
        $order['review'] = $this->db->fetchOne(
            "SELECT id, score, content, images, is_anonymous, create_time
             FROM lc_order_review
             WHERE order_id = ? AND is_delete = 0
             LIMIT 1",
            [$id]
        );

        return $order;
    }

    // ── 状态操作（事务） ────────────────────────────────────────────

    /** 取消订单 */
    public function cancel(int $id, string $reason): void
    {
        $this->db->execute(
            "UPDATE `{$this->table}`
             SET order_status = 4, cancel_reason = ?, update_time = NOW()
             WHERE id = ? AND is_delete = 0",
            [$reason, $id]
        );
    }

    /**
     * 批准退款（事务）：
     *   1. 更新订单状态 → 6（已退款），支付状态 → 2（已退款）
     *   2. 冻结对应佣金记录（status = 2）
     */
    public function refund(int $id, string $remark): void
    {
        $pdo = $this->db->getPdo();
        $pdo->beginTransaction();

        try {
            // 1. 更新订单
            $this->db->execute(
                "UPDATE `{$this->table}`
                 SET order_status = 6, pay_status = 2, cancel_reason = ?, update_time = NOW()
                 WHERE id = ? AND is_delete = 0",
                [$remark, $id]
            );

            // 2. 冻结佣金（若存在）
            $this->db->execute(
                "UPDATE lc_commission
                 SET status = 2, freeze_reason = '退款处理', update_time = NOW()
                 WHERE order_id = ? AND is_delete = 0",
                [$id]
            );

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // ── 统计 ─────────────────────────────────────────────────────────

    /**
     * 订单状态概览统计
     * 返回各状态数量 + 已支付总金额
     */
    public function getStats(): array
    {
        $db = $this->db;
        return [
            'total'          => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE is_delete = 0"),
            'pending'        => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE order_status = 0 AND is_delete = 0"),
            'confirmed'      => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE order_status = 1 AND is_delete = 0"),
            'in_service'     => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE order_status = 2 AND is_delete = 0"),
            'completed'      => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE order_status = 3 AND is_delete = 0"),
            'cancelled'      => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE order_status = 4 AND is_delete = 0"),
            'pending_refund' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE order_status = 5 AND is_delete = 0"),
            'refunded'       => (int)$db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE order_status = 6 AND is_delete = 0"),
            'total_amount'   => (float)$db->fetchColumn(
                "SELECT COALESCE(SUM(pay_amount), 0) FROM `{$this->table}` WHERE pay_status = 1 AND is_delete = 0"
            ),
        ];
    }
}
