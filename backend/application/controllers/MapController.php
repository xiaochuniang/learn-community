<?php
/**
 * 虚拟地图接口
 *
 * 小程序端（需登录 Token）：
 *   GET  /map/points     获取所有启用地图点位
 *   GET  /map/character  获取卡通人物配置
 *
 * 管理端（同样需要管理员 Token）：
 *   GET  /map/adminPoints        点位列表（分页）
 *   POST /map/savePoint          新增 / 编辑点位
 *   POST /map/deletePoint        删除点位
 *   GET  /map/characterConfig    管理端查询人物配置
 *   POST /map/saveCharacter      保存人物配置
 */
class MapController extends BaseController
{
    // ── 小程序端 ─────────────────────────────────────────────────────

    /**
     * GET /map/points
     * 返回全部启用点位列表，供小程序渲染地图
     */
    public function pointsAction(): never
    {
        $model  = new MapModel();
        $points = $model->getActivePoints();
        $this->success($points);
    }

    /**
     * GET /map/character
     * 返回卡通人物配置
     */
    public function characterAction(): never
    {
        $model     = new MapModel();
        $character = $model->getCharacter();
        $this->success($character);
    }

    // ── 管理端 ────────────────────────────────────────────────────────

    /**
     * GET /map/adminPoints?page=&limit=
     */
    public function adminPointsAction(): never
    {
        $pager  = $this->page();
        $model  = new MapModel();
        $result = $model->getAllPoints($pager['page'], $pager['limit']);
        $this->success($result);
    }

    /**
     * POST /map/savePoint
     *
     * Body: {
     *   id?, key, name, icon, description,
     *   grid_x, grid_y, build_height,
     *   color_top, color_left, color_right,
     *   target_path, is_home, sort, status
     * }
     */
    public function savePointAction(): never
    {
        $raw = $this->postBody();

        $v = new Validator($raw);
        $v->required('name',    '点位名称不能为空')
          ->required('key',     '点位标识不能为空')
          ->required('grid_x',  '网格X坐标不能为空')
          ->required('grid_y',  '网格Y坐标不能为空');

        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $data = [
            'key'          => trim((string)($raw['key']          ?? '')),
            'name'         => trim((string)($raw['name']         ?? '')),
            'icon'         => trim((string)($raw['icon']         ?? '📍')),
            'description'  => trim((string)($raw['description']  ?? '')),
            'grid_x'       => (int)($raw['grid_x']   ?? 0),
            'grid_y'       => (int)($raw['grid_y']   ?? 0),
            'build_height' => (int)($raw['build_height'] ?? 48),
            'color_top'    => trim((string)($raw['color_top']    ?? '#87CEEB')),
            'color_left'   => trim((string)($raw['color_left']   ?? '#5BA3D0')),
            'color_right'  => trim((string)($raw['color_right']  ?? '#3A7DB8')),
            'target_path'  => trim((string)($raw['target_path']  ?? '')),
            'is_home'      => (int)($raw['is_home']  ?? 0),
            'sort'         => (int)($raw['sort']     ?? 0),
            'status'       => (int)($raw['status']   ?? 1),
        ];

        if (!empty($raw['id'])) {
            $data['id'] = (int)$raw['id'];
        }

        $model = new MapModel();
        $id    = $model->savePoint($data);
        $this->success(['id' => $id]);
    }

    /**
     * POST /map/deletePoint
     *
     * Body: { id: int }
     */
    public function deletePointAction(): never
    {
        $id = (int)($this->postBody()['id'] ?? 0);
        if ($id <= 0) {
            $this->fail('参数 id 不能为空');
        }
        $model = new MapModel();
        $model->deletePoint($id);
        $this->success(null);
    }

    /**
     * GET /map/characterConfig
     */
    public function characterConfigAction(): never
    {
        $model     = new MapModel();
        $character = $model->getCharacter();
        $this->success($character);
    }

    /**
     * POST /map/saveCharacter
     *
     * Body: { body_color, skin_color, hair_color, walk_speed, home_grid_x, home_grid_y, name }
     */
    public function saveCharacterAction(): never
    {
        $raw = $this->postBody();

        $data = [
            'body_color'  => trim((string)($raw['body_color']  ?? '#FF9800')),
            'skin_color'  => trim((string)($raw['skin_color']  ?? '#FFCC80')),
            'hair_color'  => trim((string)($raw['hair_color']  ?? '#5D4037')),
            'walk_speed'  => max(0.5, min(10.0, (float)($raw['walk_speed'] ?? 2.0))),
            'home_grid_x' => (int)($raw['home_grid_x'] ?? 2),
            'home_grid_y' => (int)($raw['home_grid_y'] ?? 8),
            'name'        => trim((string)($raw['name'] ?? '小明')),
        ];

        $model = new MapModel();
        $model->saveCharacter($data);
        $this->success(null);
    }

    // ── 内部辅助 ──────────────────────────────────────────────────────

    /** 解析 JSON 请求体 */
    private function postBody(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return $this->getRequest()->getPost() ?: [];
        }
        return (array)(json_decode($raw, true) ?? []);
    }
}
