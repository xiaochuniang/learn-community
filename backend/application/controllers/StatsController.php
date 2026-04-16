<?php
/**
 * 数据统计接口
 *
 * GET /stats/dashboard   管理后台总览面板
 * GET /stats/trend       近 N 天订单量 / 营收趋势
 * GET /stats/tutorRank   陪伴师接单排行榜
 */
class StatsController extends BaseController
{
    /**
     * GET /stats/dashboard
     *
     * 返回：用户、陪伴师、订单、营收四个维度的关键数字
     */
    public function dashboardAction(): never
    {
        $db = Db::getInstance();

        // ── 用户 ────────────────────────────────────────────────────
        $userTotal = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_user WHERE is_delete = 0"
        );
        $userToday = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_user WHERE is_delete = 0 AND DATE(create_time) = CURDATE()"
        );
        $userMonth = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_user WHERE is_delete = 0
             AND DATE_FORMAT(create_time,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')"
        );

        // ── 陪伴师 ──────────────────────────────────────────────────
        $tutorTotal   = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_tutor WHERE is_delete = 0"
        );
        $tutorActive  = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_tutor WHERE audit_status = 1 AND status = 1 AND is_delete = 0"
        );
        $tutorPending = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_tutor_audit WHERE status = 0 AND is_delete = 0"
        );

        // ── 订单 ────────────────────────────────────────────────────
        $orderTotal      = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_order WHERE is_delete = 0"
        );
        $orderToday      = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_order WHERE is_delete = 0 AND DATE(create_time) = CURDATE()"
        );
        $orderPendingRef = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM lc_order WHERE order_status = 5 AND is_delete = 0"
        );

        // ── 营收 ────────────────────────────────────────────────────
        $revenueTotal = (float)$db->fetchColumn(
            "SELECT COALESCE(SUM(pay_amount), 0) FROM lc_order WHERE pay_status = 1 AND is_delete = 0"
        );
        $revenueToday = (float)$db->fetchColumn(
            "SELECT COALESCE(SUM(pay_amount), 0)
             FROM lc_order WHERE pay_status = 1 AND is_delete = 0
             AND DATE(create_time) = CURDATE()"
        );
        $revenueMonth = (float)$db->fetchColumn(
            "SELECT COALESCE(SUM(pay_amount), 0)
             FROM lc_order WHERE pay_status = 1 AND is_delete = 0
             AND DATE_FORMAT(create_time,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')"
        );

        $this->success([
            'user'    => [
                'total' => $userTotal,
                'today' => $userToday,
                'month' => $userMonth,
            ],
            'tutor'   => [
                'total'         => $tutorTotal,
                'active'        => $tutorActive,
                'pending_audit' => $tutorPending,
            ],
            'order'   => [
                'total'          => $orderTotal,
                'today'          => $orderToday,
                'pending_refund' => $orderPendingRef,
            ],
            'revenue' => [
                'total' => round($revenueTotal, 2),
                'today' => round($revenueToday, 2),
                'month' => round($revenueMonth, 2),
            ],
        ]);
    }

    /**
     * GET /stats/trend
     *
     * Query: type=order|revenue  days=7|14|30|90
     *
     * 返回近 N 天的折线图数据（缺失日期补 0）
     */
    public function trendAction(): never
    {
        $type = $this->input('type', 'order');
        $days = min(90, max(7, (int)$this->input('days', 7)));

        $v = new Validator(['type' => $type]);
        $v->in('type', ['order', 'revenue'], 'type 只能为 order 或 revenue');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $db = Db::getInstance();

        if ($type === 'revenue') {
            $rows = $db->fetchAll(
                "SELECT DATE(create_time) AS `date`, COALESCE(SUM(pay_amount), 0) AS `value`
                 FROM lc_order
                 WHERE pay_status = 1 AND is_delete = 0
                   AND create_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 GROUP BY DATE(create_time)
                 ORDER BY `date` ASC",
                [$days]
            );
        } else {
            $rows = $db->fetchAll(
                "SELECT DATE(create_time) AS `date`, COUNT(*) AS `value`
                 FROM lc_order
                 WHERE is_delete = 0
                   AND create_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 GROUP BY DATE(create_time)
                 ORDER BY `date` ASC",
                [$days]
            );
        }

        // 将查询结果转为 date => value 的 map，补全缺失日期
        $map    = array_column($rows, 'value', 'date');
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['date' => $date, 'value' => round((float)($map[$date] ?? 0), 2)];
        }

        $this->success(['type' => $type, 'days' => $days, 'list' => $result]);
    }

    /**
     * GET /stats/tutorRank
     *
     * Query: limit=10  （最多返回 50 条）
     *
     * 按累计接单数倒序，返回陪伴师排行
     */
    public function tutorRankAction(): never
    {
        $limit = min(50, max(5, (int)$this->input('limit', 10)));
        $db    = Db::getInstance();

        $rows = $db->fetchAll(
            "SELECT id, real_name, avatar, score, order_count, total_income
             FROM lc_tutor
             WHERE is_delete = 0 AND audit_status = 1
             ORDER BY order_count DESC, score DESC
             LIMIT ?",
            [$limit]
        );

        $this->success($rows);
    }
}
