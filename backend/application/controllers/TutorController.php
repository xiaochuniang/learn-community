<?php
/**
 * 陪伴师相关接口
 *
 * GET  /tutor/list           陪伴师列表（支持审核状态/关键词/分页过滤）
 * GET  /tutor/detail         陪伴师详情（含证件文件）
 * GET  /tutor/auditList      入驻审核申请列表
 * GET  /tutor/auditDetail    审核申请详情（含 submit_data 快照）
 * POST /tutor/audit          审核操作（pass / reject）
 * POST /tutor/statusChange   上线 / 下线陪伴师
 */
class TutorController extends BaseController
{
    // ── 列表 & 详情 ──────────────────────────────────────────────────

    /**
     * GET /tutor/list
     *
     * Query: audit_status=0|1|2  keyword=  page=  limit=
     */
    public function listAction(): never
    {
        $pager       = $this->page();
        $auditStatus = $this->input('audit_status', '');
        $keyword     = trim((string)$this->input('keyword', ''));

        $model  = new TutorModel();
        $result = $model->paginate($pager['page'], $pager['limit'], $auditStatus, $keyword);
        $this->success($result);
    }

    /**
     * GET /tutor/detail?id=
     */
    public function detailAction(): never
    {
        $id = (int)$this->input('id');
        if ($id <= 0) {
            $this->fail('参数 id 不能为空');
        }
        $model  = new TutorModel();
        $detail = $model->findDetailById($id);
        if ($detail === null) {
            $this->fail('陪伴师不存在', Response::NOT_FOUND);
        }
        $this->success($detail);
    }

    // ── 审核 ─────────────────────────────────────────────────────────

    /**
     * GET /tutor/auditList
     *
     * Query: status=0|1|2  page=  limit=
     */
    public function auditListAction(): never
    {
        $pager  = $this->page();
        $status = (string)$this->input('status', '0');   // 默认：待审核

        $model  = new TutorModel();
        $result = $model->getAuditList($pager['page'], $pager['limit'], $status);
        $this->success($result);
    }

    /**
     * GET /tutor/auditDetail?audit_id=
     */
    public function auditDetailAction(): never
    {
        $id = (int)$this->input('audit_id');
        if ($id <= 0) {
            $this->fail('参数 audit_id 不能为空');
        }
        $model  = new TutorModel();
        $detail = $model->findAuditDetail($id);
        if ($detail === null) {
            $this->fail('审核记录不存在', Response::NOT_FOUND);
        }
        $this->success($detail);
    }

    /**
     * POST /tutor/audit
     *
     * Body: { "audit_id": 1, "action": "pass"|"reject", "remark": "..." }
     */
    public function auditAction(): never
    {
        $data = $this->all();

        $v = new Validator($data);
        $v->required('audit_id', '审核记录 ID 不能为空')
          ->integer('audit_id', '审核记录 ID 格式错误')
          ->required('action', '审核操作不能为空')
          ->in('action', ['pass', 'reject'], 'action 只能为 pass 或 reject');
        if (($data['action'] ?? '') === 'reject') {
            $v->required('remark', '拒绝时必须填写审核意见');
        }
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $auditId = (int)$data['audit_id'];
        $model   = new TutorModel();
        $audit   = $model->findAuditById($auditId);

        if ($audit === null) {
            $this->fail('审核记录不存在', Response::NOT_FOUND);
        }
        if ((int)$audit['status'] !== 0) {
            $this->fail('该申请已处理，不可重复审核');
        }

        $isPass  = ($data['action'] === 'pass');
        $status  = $isPass ? 1 : 2;
        $remark  = trim((string)($data['remark'] ?? ''));
        $adminId = $this->getAdminId();

        $model->processAudit($auditId, (int)$audit['tutor_id'], $status, $remark, $adminId);
        $this->success(null, $isPass ? '审核通过，陪伴师已上线' : '已拒绝该申请');
    }

    // ── 上下线 ───────────────────────────────────────────────────────

    /**
     * POST /tutor/statusChange
     *
     * Body: { "tutor_id": 1, "status": 0|1 }
     */
    public function statusChangeAction(): never
    {
        $data = $this->all();

        $v = new Validator($data);
        $v->required('tutor_id', '陪伴师 ID 不能为空')
          ->integer('tutor_id', '陪伴师 ID 格式错误')
          ->in('status', [0, 1, '0', '1'], 'status 只能为 0（下线）或 1（上线）');
        if ($v->fails()) {
            $this->fail($v->firstError());
        }

        $model = new TutorModel();
        $model->changeStatus((int)$data['tutor_id'], (int)$data['status']);
        $this->success(null, (int)$data['status'] === 1 ? '陪伴师已上线' : '陪伴师已下线');
    }
}
