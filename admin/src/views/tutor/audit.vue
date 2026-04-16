<template>
  <div class="audit-page">

    <!-- ① 搜索栏 -->
    <div class="search-panel">
      <div class="search-fields">
        <el-input
          v-model="query.keyword"
          placeholder="姓名 / 手机号"
          clearable
          style="width: 180px"
          @keyup.enter="handleSearch"
        >
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>

        <el-select v-model="query.auditStatus" placeholder="审核状态" clearable style="width: 130px">
          <el-option v-for="item in AUDIT_STATUS_OPTIONS" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>

        <el-select v-model="query.identityType" placeholder="身份类型" clearable style="width: 140px">
          <el-option v-for="item in IDENTITY_OPTIONS" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>

        <el-date-picker
          v-model="dateRange"
          type="daterange"
          range-separator="至"
          start-placeholder="提交开始日"
          end-placeholder="提交结束日"
          value-format="YYYY-MM-DD"
          style="width: 240px"
        />

        <el-button type="primary" :icon="Search" @click="handleSearch">搜索</el-button>
        <el-button :icon="RefreshRight" @click="handleReset">重置</el-button>
      </div>

      <div class="toolbar-actions">
        <el-button :icon="Refresh" circle title="刷新" @click="loadData" />
        <el-button :icon="Download" @click="handleExport" :loading="exporting">导出</el-button>
      </div>
    </div>

    <!-- ② 统计卡片 -->
    <div class="stat-cards">
      <div
        v-for="card in statCards"
        :key="card.key"
        class="stat-card"
        :class="{ active: query.auditStatus === card.filterVal }"
        @click="filterByStatus(card.filterVal)"
      >
        <div class="stat-icon" :style="{ background: card.bg }">
          <el-icon :style="{ color: card.color }" :size="22"><component :is="card.icon" /></el-icon>
        </div>
        <div class="stat-body">
          <p class="stat-num">{{ stats[card.key] ?? '—' }}</p>
          <p class="stat-label">{{ card.label }}</p>
        </div>
      </div>
    </div>

    <!-- ③ 数据表格 -->
    <div class="table-panel">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        stripe
        row-key="id"
        highlight-current-row
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="44" fixed="left" />
        <el-table-column type="index" label="序号" width="60" fixed="left" />

        <el-table-column label="申请人" width="180" fixed="left">
          <template #default="{ row }">
            <div class="tutor-cell">
              <el-avatar :size="32" :src="row.avatar" icon="UserFilled" />
              <div class="tutor-meta">
                <span class="tutor-name">{{ row.realName }}</span>
                <span class="tutor-mobile">{{ maskMobile(row.mobile) }}</span>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="身份类型" width="110">
          <template #default="{ row }">
            <el-tag :type="IDENTITY_TAG_TYPE[row.identityType] || ''" size="small">
              {{ IDENTITY_MAP[row.identityType] || '-' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="学历" width="80">
          <template #default="{ row }">{{ EDUCATION_MAP[row.education] || '-' }}</template>
        </el-table-column>

        <el-table-column prop="school" label="院校" min-width="130" show-overflow-tooltip />
        <el-table-column prop="subjects" label="辅导科目" min-width="120" show-overflow-tooltip />

        <el-table-column prop="servicePrice" label="单价(元/h)" width="100" align="right" />

        <el-table-column label="申请类型" width="100">
          <template #default="{ row }">
            <el-tag :type="row.auditType === 1 ? 'primary' : 'warning'" size="small">
              {{ row.auditType === 1 ? '入驻申请' : '资质变更' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="审核状态" width="100">
          <template #default="{ row }">
            <el-tag :type="AUDIT_STATUS_TAG[row.auditStatus]" size="small">
              {{ AUDIT_STATUS_MAP[row.auditStatus] || '-' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="复核状态" width="90">
          <template #default="{ row }">
            <el-tag v-if="row.reviewStatus === 1" type="warning" size="small">待复核</el-tag>
            <el-tag v-else-if="row.reviewStatus === 2" type="success" size="small">已复核</el-tag>
            <span v-else class="text-placeholder">—</span>
          </template>
        </el-table-column>

        <el-table-column label="提交时间" width="160">
          <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
        </el-table-column>

        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button size="small" link type="primary" @click="openDetail(row.id)">
              详情
            </el-button>

            <template v-if="row.auditStatus === 0">
              <el-button size="small" link type="success" @click="handleApprove(row)">
                通过
              </el-button>
              <el-button size="small" link type="danger" @click="openReject(row)">
                驳回
              </el-button>
            </template>

            <el-button
              v-if="row.auditStatus !== 3"
              size="small"
              link
              type="warning"
              @click="openBlacklist(row)"
            >
              拉黑
            </el-button>

            <el-button
              v-if="row.auditStatus === 2 && row.reviewStatus !== 1"
              size="small"
              link
              @click="openReview(row)"
            >
              复核
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 批量操作栏 -->
      <div v-if="selection.length > 0" class="batch-bar">
        <span>已选 <b>{{ selection.length }}</b> 条</span>
        <el-button size="small" type="success" @click="batchApprove">批量通过</el-button>
        <el-button size="small" type="danger" @click="batchReject">批量驳回</el-button>
      </div>

      <!-- 分页 -->
      <div class="pagination-wrap">
        <el-pagination
          v-model:current-page="query.page"
          v-model:page-size="query.pageSize"
          :total="total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @change="loadData"
        />
      </div>
    </div>

    <!-- ④ 驳回弹窗 -->
    <el-dialog
      v-model="rejectDialog.visible"
      title="驳回审核"
      width="480px"
      :close-on-click-modal="false"
      @closed="rejectDialog.remark = ''"
    >
      <el-form ref="rejectFormRef" :model="rejectDialog" :rules="rejectRules" label-width="80px">
        <el-form-item label="申请人">
          <span class="dialog-info">{{ rejectDialog.realName }}</span>
        </el-form-item>
        <el-form-item label="驳回原因" prop="remark">
          <el-input
            v-model="rejectDialog.remark"
            type="textarea"
            :rows="4"
            placeholder="请填写驳回原因，将通知申请人（必填）"
            maxlength="200"
            show-word-limit
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="rejectDialog.visible = false">取消</el-button>
        <el-button type="danger" :loading="rejectDialog.submitting" @click="submitReject">
          确认驳回
        </el-button>
      </template>
    </el-dialog>

    <!-- ⑤ 拉黑弹窗 -->
    <el-dialog
      v-model="blacklistDialog.visible"
      title="拉黑陪伴师"
      width="480px"
      :close-on-click-modal="false"
    >
      <el-alert type="warning" :closable="false" style="margin-bottom:16px">
        拉黑后该陪伴师将永久无法在平台接单，请谨慎操作！
      </el-alert>
      <el-form ref="blacklistFormRef" :model="blacklistDialog" :rules="blacklistRules" label-width="80px">
        <el-form-item label="拉黑原因" prop="reason">
          <el-input
            v-model="blacklistDialog.reason"
            type="textarea"
            :rows="4"
            placeholder="请填写拉黑原因（必填）"
            maxlength="200"
            show-word-limit
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="blacklistDialog.visible = false">取消</el-button>
        <el-button type="danger" :loading="blacklistDialog.submitting" @click="submitBlacklist">
          确认拉黑
        </el-button>
      </template>
    </el-dialog>

    <!-- ⑥ 复核弹窗 -->
    <el-dialog
      v-model="reviewDialog.visible"
      title="发起复核"
      width="480px"
      :close-on-click-modal="false"
    >
      <el-form ref="reviewFormRef" :model="reviewDialog" :rules="reviewRules" label-width="80px">
        <el-form-item label="复核说明" prop="remark">
          <el-input
            v-model="reviewDialog.remark"
            type="textarea"
            :rows="4"
            placeholder="请说明发起复核的原因"
            maxlength="200"
            show-word-limit
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="reviewDialog.visible = false">取消</el-button>
        <el-button type="primary" :loading="reviewDialog.submitting" @click="submitReview">
          提交复核
        </el-button>
      </template>
    </el-dialog>

    <!-- ⑦ 详情弹窗 -->
    <el-dialog
      v-model="detailDialog.visible"
      title="陪伴师资质详情"
      width="800px"
      top="5vh"
      :close-on-click-modal="false"
    >
      <div v-loading="detailDialog.loading" class="detail-wrap">
        <template v-if="detailDialog.data">
          <!-- 基本信息 -->
          <div class="detail-section">
            <div class="section-title">基本信息</div>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="di-label">姓名</span>
                <span class="di-val">{{ detailDialog.data.realName }}</span>
              </div>
              <div class="detail-item">
                <span class="di-label">手机号</span>
                <span class="di-val">{{ maskMobile(detailDialog.data.mobile) }}</span>
              </div>
              <div class="detail-item">
                <span class="di-label">身份类型</span>
                <span class="di-val">
                  <el-tag :type="IDENTITY_TAG_TYPE[detailDialog.data.identityType] || ''" size="small">
                    {{ IDENTITY_MAP[detailDialog.data.identityType] || '-' }}
                  </el-tag>
                </span>
              </div>
              <div class="detail-item">
                <span class="di-label">学历</span>
                <span class="di-val">{{ EDUCATION_MAP[detailDialog.data.education] || '-' }}</span>
              </div>
              <div class="detail-item">
                <span class="di-label">院校</span>
                <span class="di-val">{{ detailDialog.data.school }}</span>
              </div>
              <div class="detail-item">
                <span class="di-label">专业</span>
                <span class="di-val">{{ detailDialog.data.major || '-' }}</span>
              </div>
              <div class="detail-item">
                <span class="di-label">辅导科目</span>
                <span class="di-val">{{ detailDialog.data.subjects }}</span>
              </div>
              <div class="detail-item">
                <span class="di-label">单价(元/h)</span>
                <span class="di-val">{{ detailDialog.data.servicePrice }}</span>
              </div>
              <div class="detail-item">
                <span class="di-label">累计接单</span>
                <span class="di-val">{{ detailDialog.data.orderCount }} 次</span>
              </div>
              <div class="detail-item">
                <span class="di-label">评分</span>
                <span class="di-val">{{ detailDialog.data.score || '-' }}</span>
              </div>
              <div class="detail-item full">
                <span class="di-label">擅长标签</span>
                <span class="di-val">
                  <el-tag
                    v-for="tag in detailDialog.data.tags"
                    :key="tag"
                    size="small"
                    style="margin: 2px"
                  >{{ tag }}</el-tag>
                </span>
              </div>
              <div class="detail-item full">
                <span class="di-label">自我介绍</span>
                <span class="di-val pre-wrap">{{ detailDialog.data.selfIntro || '-' }}</span>
              </div>
              <div class="detail-item full">
                <span class="di-label">服务说明</span>
                <span class="di-val pre-wrap">{{ detailDialog.data.serviceDesc || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- 证件图片 -->
          <div class="detail-section">
            <div class="section-title">证件材料</div>
            <div class="cert-images">
              <div class="cert-item" v-if="detailDialog.data.idCardFront">
                <p class="cert-label">身份证（正面）</p>
                <el-image
                  :src="detailDialog.data.idCardFront"
                  :preview-src-list="certImageList(detailDialog.data)"
                  fit="cover"
                  class="cert-img"
                />
              </div>
              <div class="cert-item" v-if="detailDialog.data.idCardBack">
                <p class="cert-label">身份证（背面）</p>
                <el-image
                  :src="detailDialog.data.idCardBack"
                  :preview-src-list="certImageList(detailDialog.data)"
                  fit="cover"
                  class="cert-img"
                />
              </div>
              <div class="cert-item" v-if="detailDialog.data.educationCert">
                <p class="cert-label">学历证书</p>
                <el-image
                  :src="detailDialog.data.educationCert"
                  :preview-src-list="certImageList(detailDialog.data)"
                  fit="cover"
                  class="cert-img"
                />
              </div>
              <div class="cert-item" v-if="detailDialog.data.teacherCert">
                <p class="cert-label">教师资格证</p>
                <el-image
                  :src="detailDialog.data.teacherCert"
                  :preview-src-list="certImageList(detailDialog.data)"
                  fit="cover"
                  class="cert-img"
                />
              </div>
            </div>
          </div>

          <!-- 审核状态 -->
          <div class="detail-section">
            <div class="section-title">审核状态</div>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="di-label">当前状态</span>
                <span class="di-val">
                  <el-tag :type="AUDIT_STATUS_TAG[detailDialog.data.auditStatus]" size="small">
                    {{ AUDIT_STATUS_MAP[detailDialog.data.auditStatus] }}
                  </el-tag>
                </span>
              </div>
              <div class="detail-item" v-if="detailDialog.data.auditTime">
                <span class="di-label">审核时间</span>
                <span class="di-val">{{ formatDateTime(detailDialog.data.auditTime) }}</span>
              </div>
              <div class="detail-item" v-if="detailDialog.data.auditorName">
                <span class="di-label">审核人</span>
                <span class="di-val">{{ detailDialog.data.auditorName }}</span>
              </div>
              <div class="detail-item full" v-if="detailDialog.data.auditRemark">
                <span class="di-label">审核意见</span>
                <span class="di-val">{{ detailDialog.data.auditRemark }}</span>
              </div>
              <div class="detail-item full" v-if="detailDialog.data.blackReason">
                <span class="di-label">拉黑原因</span>
                <span class="di-val" style="color:#f56c6c">{{ detailDialog.data.blackReason }}</span>
              </div>
            </div>
          </div>

          <!-- 历史审核记录 -->
          <div class="detail-section" v-if="detailDialog.data.historyAudits?.length">
            <div class="section-title">历史审核记录</div>
            <el-timeline>
              <el-timeline-item
                v-for="log in detailDialog.data.historyAudits"
                :key="log.id"
                :timestamp="formatDateTime(log.createTime)"
                placement="top"
              >
                <div class="timeline-content">
                  <b>{{ log.action }}</b>
                  <span v-if="log.remark" class="timeline-remark">{{ log.remark }}</span>
                  <span class="timeline-operator">操作人：{{ log.operatorName }}</span>
                </div>
              </el-timeline-item>
            </el-timeline>
          </div>
        </template>
      </div>

      <template #footer>
        <template v-if="detailDialog.data && detailDialog.data.auditStatus === 0">
          <el-button type="success" @click="handleApproveFromDetail">审核通过</el-button>
          <el-button type="danger" @click="handleRejectFromDetail">审核驳回</el-button>
        </template>
        <el-button @click="detailDialog.visible = false">关闭</el-button>
      </template>
    </el-dialog>

  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import {
  Search,
  Refresh,
  RefreshRight,
  Download,
  Clock,
  CircleCheck,
  CircleClose,
  Warning,
  List
} from '@element-plus/icons-vue'
import {
  getTutorAuditList,
  getTutorAuditDetail,
  getTutorAuditStats,
  approveTutor,
  rejectTutor,
  blacklistTutor,
  requestReview,
  exportAuditList,
  type TutorAuditItem,
  type TutorAuditDetail,
  type TutorAuditStats,
  type TutorListQuery
} from '@/api/tutor'
import { formatDateTime, maskMobile } from '@/utils/format'

// ─────────────────────────────────────────────
//  常量映射
// ─────────────────────────────────────────────

const AUDIT_STATUS_OPTIONS = [
  { label: '待审核', value: 0 },
  { label: '已通过', value: 1 },
  { label: '已驳回', value: 2 },
  { label: '已拉黑', value: 3 }
]

const AUDIT_STATUS_MAP: Record<number, string> = {
  0: '待审核',
  1: '已通过',
  2: '已驳回',
  3: '已拉黑'
}

const AUDIT_STATUS_TAG: Record<number, '' | 'success' | 'warning' | 'danger' | 'info'> = {
  0: 'warning',
  1: 'success',
  2: 'danger',
  3: 'info'
}

const IDENTITY_OPTIONS = [
  { label: '在校大学生', value: 1 },
  { label: '应届毕业生', value: 2 },
  { label: '在职教师', value: 3 },
  { label: '自由职业', value: 4 }
]

const IDENTITY_MAP: Record<number, string> = {
  1: '在校大学生',
  2: '应届毕业生',
  3: '在职教师',
  4: '自由职业'
}

const IDENTITY_TAG_TYPE: Record<number, '' | 'success' | 'warning' | 'danger' | 'info'> = {
  1: '',
  2: 'success',
  3: 'warning',
  4: 'info'
}

const EDUCATION_MAP: Record<number, string> = {
  1: '专科',
  2: '本科',
  3: '硕士',
  4: '博士'
}

// ─────────────────────────────────────────────
//  统计卡片配置
// ─────────────────────────────────────────────

const statCards = [
  { key: 'total' as keyof TutorAuditStats, label: '累计申请', icon: 'List', bg: '#e8f4ff', color: '#1890ff', filterVal: undefined },
  { key: 'pending' as keyof TutorAuditStats, label: '待审核', icon: 'Clock', bg: '#fff7e6', color: '#fa8c16', filterVal: 0 },
  { key: 'approved' as keyof TutorAuditStats, label: '已通过', icon: 'CircleCheck', bg: '#f6ffed', color: '#52c41a', filterVal: 1 },
  { key: 'rejected' as keyof TutorAuditStats, label: '已驳回', icon: 'CircleClose', bg: '#fff1f0', color: '#f5222d', filterVal: 2 },
  { key: 'blacklisted' as keyof TutorAuditStats, label: '已拉黑', icon: 'Warning', bg: '#f9f9f9', color: '#909399', filterVal: 3 }
]

// ─────────────────────────────────────────────
//  搜索 & 列表
// ─────────────────────────────────────────────

const loading = ref(false)
const tableData = ref<TutorAuditItem[]>([])
const total = ref(0)
const selection = ref<TutorAuditItem[]>([])
const dateRange = ref<[string, string] | null>(null)
const exporting = ref(false)

const query = reactive<TutorListQuery>({
  page: 1,
  pageSize: 10,
  keyword: '',
  auditStatus: undefined,
  identityType: undefined
})

const stats = ref<TutorAuditStats>({ total: 0, pending: 0, approved: 0, rejected: 0, blacklisted: 0 })

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { ...query }
    if (dateRange.value) {
      params.startDate = dateRange.value[0]
      params.endDate = dateRange.value[1]
    }
    const res = await getTutorAuditList(params as TutorListQuery)
    tableData.value = res.data.list
    total.value = res.data.total
  } finally {
    loading.value = false
  }
}

async function loadStats() {
  const res = await getTutorAuditStats()
  stats.value = res.data
}

function handleSearch() {
  query.page = 1
  loadData()
}

function handleReset() {
  query.keyword = ''
  query.auditStatus = undefined
  query.identityType = undefined
  dateRange.value = null
  query.page = 1
  loadData()
  loadStats()
}

function filterByStatus(val: number | undefined) {
  query.auditStatus = val
  query.page = 1
  loadData()
}

function handleSelectionChange(rows: TutorAuditItem[]) {
  selection.value = rows
}

async function handleExport() {
  exporting.value = true
  try {
    await exportAuditList({ ...query })
    ElMessage.success('导出成功，请查看下载文件')
  } finally {
    exporting.value = false
  }
}

// ─────────────────────────────────────────────
//  审核通过
// ─────────────────────────────────────────────

async function handleApprove(row: TutorAuditItem) {
  await ElMessageBox.confirm(
    `确定审核通过「${row.realName}」的入驻申请吗？`,
    '审核通过',
    { type: 'success', confirmButtonText: '确认通过', cancelButtonText: '取消' }
  )
  await approveTutor(row.id)
  ElMessage.success('审核通过')
  loadData()
  loadStats()
}

async function batchApprove() {
  const pending = selection.value.filter((r) => r.auditStatus === 0)
  if (!pending.length) return ElMessage.warning('所选记录中无待审核条目')
  await ElMessageBox.confirm(`确定批量通过 ${pending.length} 条申请？`, '批量通过', { type: 'success' })
  await Promise.all(pending.map((r) => approveTutor(r.id)))
  ElMessage.success('批量通过成功')
  loadData()
  loadStats()
}

async function batchReject() {
  const pending = selection.value.filter((r) => r.auditStatus === 0)
  if (!pending.length) return ElMessage.warning('所选记录中无待审核条目')
  const { value: remark } = await ElMessageBox.prompt('请填写批量驳回原因', '批量驳回', {
    inputType: 'textarea',
    inputValidator: (v) => (v && v.trim() ? true : '驳回原因不能为空'),
    type: 'warning'
  })
  await Promise.all(pending.map((r) => rejectTutor(r.id, remark as string)))
  ElMessage.success('批量驳回成功')
  loadData()
  loadStats()
}

// ─────────────────────────────────────────────
//  驳回弹窗
// ─────────────────────────────────────────────

const rejectFormRef = ref<FormInstance>()
const rejectDialog = reactive({
  visible: false,
  submitting: false,
  tutorId: 0,
  realName: '',
  remark: ''
})
const rejectRules: FormRules = {
  remark: [{ required: true, message: '驳回原因不能为空', trigger: 'blur' },
           { min: 5, message: '至少填写 5 个字', trigger: 'blur' }]
}

function openReject(row: TutorAuditItem) {
  rejectDialog.tutorId = row.id
  rejectDialog.realName = row.realName
  rejectDialog.remark = ''
  rejectDialog.visible = true
}

async function submitReject() {
  await rejectFormRef.value?.validate()
  rejectDialog.submitting = true
  try {
    await rejectTutor(rejectDialog.tutorId, rejectDialog.remark)
    ElMessage.success('已驳回')
    rejectDialog.visible = false
    loadData()
    loadStats()
  } finally {
    rejectDialog.submitting = false
  }
}

// ─────────────────────────────────────────────
//  拉黑弹窗
// ─────────────────────────────────────────────

const blacklistFormRef = ref<FormInstance>()
const blacklistDialog = reactive({
  visible: false,
  submitting: false,
  tutorId: 0,
  reason: ''
})
const blacklistRules: FormRules = {
  reason: [{ required: true, message: '拉黑原因不能为空', trigger: 'blur' },
           { min: 5, message: '至少填写 5 个字', trigger: 'blur' }]
}

function openBlacklist(row: TutorAuditItem) {
  blacklistDialog.tutorId = row.id
  blacklistDialog.reason = ''
  blacklistDialog.visible = true
}

async function submitBlacklist() {
  await blacklistFormRef.value?.validate()
  blacklistDialog.submitting = true
  try {
    await blacklistTutor(blacklistDialog.tutorId, blacklistDialog.reason)
    ElMessage.success('已拉黑')
    blacklistDialog.visible = false
    loadData()
    loadStats()
  } finally {
    blacklistDialog.submitting = false
  }
}

// ─────────────────────────────────────────────
//  复核弹窗
// ─────────────────────────────────────────────

const reviewFormRef = ref<FormInstance>()
const reviewDialog = reactive({
  visible: false,
  submitting: false,
  tutorId: 0,
  remark: ''
})
const reviewRules: FormRules = {
  remark: [{ required: true, message: '复核说明不能为空', trigger: 'blur' }]
}

function openReview(row: TutorAuditItem) {
  reviewDialog.tutorId = row.id
  reviewDialog.remark = ''
  reviewDialog.visible = true
}

async function submitReview() {
  await reviewFormRef.value?.validate()
  reviewDialog.submitting = true
  try {
    await requestReview(reviewDialog.tutorId, reviewDialog.remark)
    ElMessage.success('已发起复核')
    reviewDialog.visible = false
    loadData()
  } finally {
    reviewDialog.submitting = false
  }
}

// ─────────────────────────────────────────────
//  详情弹窗
// ─────────────────────────────────────────────

const detailDialog = reactive<{
  visible: boolean
  loading: boolean
  data: TutorAuditDetail | null
}>({
  visible: false,
  loading: false,
  data: null
})

async function openDetail(id: number) {
  detailDialog.visible = true
  detailDialog.loading = true
  detailDialog.data = null
  try {
    const res = await getTutorAuditDetail(id)
    detailDialog.data = res.data
  } finally {
    detailDialog.loading = false
  }
}

function certImageList(data: TutorAuditDetail): string[] {
  return [data.idCardFront, data.idCardBack, data.educationCert, data.teacherCert].filter(Boolean) as string[]
}

function handleApproveFromDetail() {
  if (!detailDialog.data) return
  detailDialog.visible = false
  handleApprove(detailDialog.data as unknown as TutorAuditItem)
}

function handleRejectFromDetail() {
  if (!detailDialog.data) return
  detailDialog.visible = false
  openReject(detailDialog.data as unknown as TutorAuditItem)
}

// ─────────────────────────────────────────────
//  初始化
// ─────────────────────────────────────────────

onMounted(() => {
  loadData()
  loadStats()
})
</script>

<style scoped>
/* ── 页面容器 ─────────────────────────── */
.audit-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* ── 搜索栏 ──────────────────────────── */
.search-panel {
  background: #fff;
  border-radius: 8px;
  padding: 16px 20px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.search-fields {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  flex: 1;
}

.toolbar-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

/* ── 统计卡片 ─────────────────────────── */
.stat-cards {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
}

@media (max-width: 1200px) {
  .stat-cards { grid-template-columns: repeat(3, 1fr); }
}

.stat-card {
  background: #fff;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  cursor: pointer;
  border: 2px solid transparent;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  transition: border-color 0.2s, transform 0.15s;
  user-select: none;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-card.active {
  border-color: var(--el-color-primary);
}

.stat-icon {
  width: 46px;
  height: 46px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-num {
  font-size: 24px;
  font-weight: 700;
  color: #303133;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 12px;
  color: #909399;
}

/* ── 表格区 ──────────────────────────── */
.table-panel {
  background: #fff;
  border-radius: 8px;
  padding: 16px 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.tutor-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.tutor-meta {
  display: flex;
  flex-direction: column;
}

.tutor-name {
  font-size: 13px;
  font-weight: 500;
  color: #303133;
  line-height: 1.4;
}

.tutor-mobile {
  font-size: 12px;
  color: #909399;
  line-height: 1.4;
}

.text-placeholder {
  color: #c0c4cc;
  font-size: 13px;
}

/* 批量操作栏 */
.batch-bar {
  margin-top: 12px;
  padding: 10px 16px;
  background: #ecf5ff;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13px;
  color: #606266;
}

/* 分页 */
.pagination-wrap {
  margin-top: 16px;
  display: flex;
  justify-content: flex-end;
}

/* ── 弹窗公共 ─────────────────────────── */
.dialog-info {
  font-size: 14px;
  color: #303133;
  font-weight: 500;
}

/* ── 详情弹窗 ─────────────────────────── */
.detail-wrap {
  max-height: calc(80vh - 120px);
  overflow-y: auto;
  padding-right: 4px;
}

.detail-section {
  margin-bottom: 24px;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #303133;
  padding: 0 0 10px;
  border-bottom: 1px solid #ebeef5;
  margin-bottom: 16px;
  position: relative;
}

.section-title::before {
  content: '';
  display: inline-block;
  width: 3px;
  height: 14px;
  background: var(--el-color-primary);
  border-radius: 2px;
  margin-right: 8px;
  vertical-align: middle;
  position: relative;
  top: -1px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px 24px;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.detail-item.full {
  grid-column: 1 / -1;
}

.di-label {
  flex-shrink: 0;
  width: 74px;
  font-size: 13px;
  color: #909399;
  text-align: right;
}

.di-val {
  font-size: 13px;
  color: #303133;
  flex: 1;
  word-break: break-all;
}

.pre-wrap {
  white-space: pre-wrap;
  line-height: 1.6;
}

/* 证件图片 */
.cert-images {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.cert-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.cert-label {
  font-size: 12px;
  color: #606266;
}

.cert-img {
  width: 160px;
  height: 110px;
  border-radius: 6px;
  border: 1px solid #ebeef5;
  object-fit: cover;
  cursor: zoom-in;
}

/* 时间线 */
.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 13px;
}

.timeline-remark {
  color: #606266;
}

.timeline-operator {
  color: #909399;
  font-size: 12px;
}
</style>
