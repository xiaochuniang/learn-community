<?php
/**
 * 用户管理接口
 *
 * GET  /user/list    用户列表（关键词 / 用户类型 / 状态 / 分页）
 * GET  /user/detail  用户详情
 * POST /user/status  启用 / 禁用用户
 * GET  /user/orders  用户订单列表
 */
class UserController extends BaseController
{
    /**
     * GET /user/list
     *
     * Query: keyword=  status=0|1  user_type=1|2  page=  limit=
     */
    public function listAction(): never
    {
        $pager    = $this->page();
        $keyword  = trim((string)$this->input('keyword', ''));
        $status   = $this->input('status', '');
        $userType = $this->input('user_type', '');

        $v = new Validator(['status' => $status, 'user_type' => $userType]);
        $v->in('status',    ['', '0', '1', 0, 1], '状态值不合法')
          ->in('user_type', ['', '1', '2', 1, 2], '用户类型不合法');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $model  = new UserModel();
        $result = $model->paginate($pager['page'], $pager['limit'], $keyword, $status, $userType);
        $this->success($result);
    }

    /**
     * GET /user/detail?id=
     */
    public function detailAction(): never
    {
        $id = (int)$this->input('id');
        if ($id <= 0) {
            $this->fail('参数 id 不能为空');
        }
        $model = new UserModel();
        $user  = $model->findById($id);
        if ($user === null) {
            $this->fail('用户不存在', Response::NOT_FOUND);
        }
        $this->success($user);
    }

    /**
     * POST /user/status
     *
     * Body: { "user_id": 1, "status": 0|1 }
     */
    public function statusAction(): never
    {
        $data = $this->all();

        $v = new Validator($data);
        $v->required('user_id', '用户 ID 不能为空')
          ->integer('user_id', '用户 ID 格式错误')
          ->in('status', [0, 1, '0', '1'], 'status 只能为 0（禁用）或 1（启用）');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $model = new UserModel();
        $user  = $model->findById((int)$data['user_id']);
        if ($user === null) {
            $this->fail('用户不存在', Response::NOT_FOUND);
        }

        $model->changeStatus((int)$data['user_id'], (int)$data['status']);
        $this->success(null, (int)$data['status'] === 1 ? '用户已启用' : '用户已禁用');
    }

    /**
     * GET /user/orders?user_id=&page=&limit=
     * 查看指定用户的订单列表
     */
    public function ordersAction(): never
    {
        $userId = (int)$this->input('user_id');
        if ($userId <= 0) {
            $this->fail('参数 user_id 不能为空');
        }
        $pager  = $this->page();
        $model  = new OrderModel();
        $result = $model->paginateByUser($userId, $pager['page'], $pager['limit']);
        $this->success($result);
    }
}
