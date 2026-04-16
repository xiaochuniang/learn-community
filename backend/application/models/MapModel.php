<?php
/**
 * 虚拟地图数据层
 *
 * 表：lc_map_point    — 地图点位
 *     lc_map_character — 人物全局配置（只有一条记录）
 */
class MapModel extends BaseModel
{
    protected string $table = 'lc_map_point';

    // ── 点位 ──────────────────────────────────────────────────────────

    /**
     * 获取所有启用点位（小程序端）
     */
    public function getActivePoints(): array
    {
        $sql = "SELECT id, `key`, `name`, icon, description,
                       grid_x, grid_y, build_height,
                       color_top, color_left, color_right,
                       target_path, is_home, sort
                FROM lc_map_point
                WHERE status = 1 AND is_delete = 0
                ORDER BY sort ASC, id ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * 获取所有点位（管理端，含禁用）
     */
    public function getAllPoints(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        $total  = (int)(($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM lc_map_point WHERE is_delete=0"
        ) ?? ['c' => 0])['c']);
        $list   = $this->db->fetchAll(
            "SELECT * FROM lc_map_point WHERE is_delete=0
             ORDER BY sort ASC, id ASC LIMIT {$limit} OFFSET {$offset}"
        );
        return $this->buildPageResult($page, $limit, $list, $total);
    }

    /**
     * 保存点位（新增或更新）
     */
    public function savePoint(array $data): int
    {
        if (!empty($data['id'])) {
            $id = (int)$data['id'];
            unset($data['id']);
            $data['update_time'] = date('Y-m-d H:i:s');
            $this->db->update('lc_map_point', $data, ['id' => $id]);
            return $id;
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        return $this->db->insert('lc_map_point', $data);
    }

    /**
     * 软删除点位
     */
    public function deletePoint(int $id): void
    {
        $this->db->update(
            'lc_map_point',
            ['is_delete' => 1, 'update_time' => date('Y-m-d H:i:s')],
            ['id' => $id]
        );
    }

    // ── 人物配置 ──────────────────────────────────────────────────────

    /**
     * 获取人物配置（不存在则返回默认值）
     */
    public function getCharacter(): array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM lc_map_character ORDER BY id ASC LIMIT 1"
        );
        if (!$row) {
            return $this->defaultCharacter();
        }
        return $row;
    }

    /**
     * 保存人物配置（upsert：只有一条配置行）
     */
    public function saveCharacter(array $data): void
    {
        $existing = $this->db->fetchOne(
            "SELECT id FROM lc_map_character LIMIT 1"
        );
        $data['update_time'] = date('Y-m-d H:i:s');
        if ($existing) {
            $this->db->update(
                'lc_map_character', $data,
                ['id' => (int)$existing['id']]
            );
        } else {
            $data['create_time'] = date('Y-m-d H:i:s');
            $this->db->insert('lc_map_character', $data);
        }
    }

    // ── 内部 ──────────────────────────────────────────────────────────

    private function defaultCharacter(): array
    {
        return [
            'id'              => 0,
            'body_color'      => '#FF9800',
            'skin_color'      => '#FFCC80',
            'hair_color'      => '#5D4037',
            'walk_speed'      => 2.0,
            'home_grid_x'     => 2,
            'home_grid_y'     => 8,
            'name'            => '小明',
        ];
    }
}
