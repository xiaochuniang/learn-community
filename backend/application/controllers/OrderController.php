<?php
/**
 * 订单管理接口
 *
 * GET  /order/list    订单列表（多维度过滤 + 分页）
 * GET  /order/detail  订单详情（含评价）
 * POST /order/cancel  强制取消订单
 * POST /order/refund  批准退款
 * GET  /order/stats   订单状态概览统计
 */
class OrderController extends BaseController
{
    /**
     * GET /order/list
     *
     * Query: order_status=  pay_status=  keyword=  date_start=  date_end=  page=  limit=
     */
    public function listAction(): never
    {
        $pager   = $this->page();
        $filters = [
            'order_status' => $this->input('order_status', ''),
            'pay_status'   => $this->input('pay_status',   ''),
            'keyword'      => trim((string)$this->input('keyword', '')),
            'date_start'   => trim((string)$this->input('date_start', '')),
            'date_end'     => trim((string)$this->input('date_end',   '')),
        ];

        $v = new Validator($filters);
        $v->date('date_start', '开始日期格式不正确')
          ->date('date_end',   '结束日期格式不正确');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $model  = new OrderModel();
        $result = $model->paginate($pager['page'], $pager['limit'], $filters);
        $this->success($result);
    }

    /**
     * GET /order/detail?id=
     */
    public function detailAction(): never
    {
        $id = (int)$this->input('id');
        if ($id <= 0) {
            $this->fail('参数 id 不能为空');
        }
        $model = new OrderModel();
        $order = $model->findDetailById($id);
        if ($order === null) {
            $this->fail('订单不存在', Response::NOT_FOUND);
        }
        $this->success($order);
    }

    /**
     * POST /order/cancel
     *
     * Body: { "order_id": 1, "reason": "违规操作" }
     */
    public function cancelAction(): never
    {
        $data = $this->all();

        $v = new Validator($data);
        $v->required('order_id', '订单 ID 不能为空')
          ->integer('order_id', '订单 ID 格式错误')
          ->required('reason',   '取消原因不能为空')
          ->maxLen('reason', 255, '取消原因不超过255字');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $model = new OrderModel();
        $order = $model->findById((int)$data['order_id']);
        if ($order === null) {
            $this->fail('订单不存在', Response::NOT_FOUND);
        }
        // 已完结/已取消/已退款 的订单不允许再取消
        if (in_array((int)$order['order_status'], [3, 4, 6], true)) {
            $this->fail('订单已完结，无法取消');
        }

        $model->cancel((int)$data['order_id'], trim((string)$data['reason']));
        $this->success(null, '订单已取消');
    }

    /**
     * POST /order/refund
     *
     * Body: { "order_id": 1, "remark": "用户投诉，批准退款" }
     */
    public function refundAction(): never
    {
        $data = $this->all();

        $v = new Validator($data);
        $v->required('order_id', '订单 ID 不能为空')
          ->integer('order_id', '订单 ID 格式错误');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $model = new OrderModel();
        $order = $model->findById((int)$data['order_id']);
        if ($order === null) {
            $this->fail('订单不存在', Response::NOT_FOUND);
        }
        if ((int)$order['order_status'] !== 5) {
            $this->fail('该订单没有待处理的退款申请');
        }
        if ((int)$order['pay_status'] !== 1) {
            $this->fail('订单尚未支付，无需退款');
        }

        $remark = trim((string)($data['remark'] ?? ''));
        $model->refund((int)$data['order_id'], $remark);
        $this->success(null, '退款处理成功');
    }

    /**
     * GET /order/stats
     * 订单状态概览数字
     */
    public function statsAction(): never
    {
        $model = new OrderModel();
        $this->success($model->getStats());
    }
}
