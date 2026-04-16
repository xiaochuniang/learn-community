<template>
  <div class="page-card">
    <div class="search-bar">
      <el-input v-model="query.keyword" placeholder="搜索姓名" clearable style="width:200px" @keyup.enter="loadData" />
      <el-select v-model="query.auditStatus" placeholder="审核状态" clearable style="width:130px">
        <el-option label="待审核" :value="0" />
        <el-option label="已通过" :value="1" />
        <el-option label="已拒绝" :value="2" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
    </div>

    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="realName" label="姓名" width="100" />
      <el-table-column label="手机号" width="140">
        <template #default="{ row }">{{ maskMobile(row.mobile) }}</template>
      </el-table-column>
      <el-table-column prop="school" label="院校" min-width="130" show-overflow-tooltip />
      <el-table-column label="审核类型" width="100">
        <template #default="{ row }">
          <el-tag size="small">{{ row.auditType === 1 ? '入驻申请' : '资质变更' }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="审核状态" width="100">
        <template #default="{ row }">
          <el-tag :type="['warning','success','danger'][row.auditStatus]" size="small">
            {{ ['待审核','已通过','已拒绝'][row.auditStatus] }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="提交时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
      <el-table-column label="操作" width="180" fixed="right">
        <template #default="{ row }">
          <template v-if="row.auditStatus === 0">
            <el-button size="small" type="success" link @click="openAudit(row, 1)">通过</el-button>
            <el-button size="small" type="danger" link @click="openAudit(row, 2)">拒绝</el-button>
          </template>
          <span v-else class="text-gray">已处理</span>
        </template>
      </el-table-column>
    </el-table>

    <el-pagination v-model:current-page="query.page" v-model:page-size="query.pageSize" :total="total"
      :page-sizes="[10,20,50]" layout="total, sizes, prev, pager, next" class="pagination" @change="loadData" />

    <!-- 审核弹窗 -->
    <el-dialog v-model="dialogVisible" :title="auditForm.status === 1 ? '通过审核' : '拒绝审核'" width="480px">
      <el-form :model="auditForm" label-width="80px">
        <el-form-item label="审核意见">
          <el-input v-model="auditForm.remark" type="textarea" :rows="3" placeholder="请填写审核意见（拒绝时必填）" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="submitAudit">确认</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import { getTutorAuditList, auditTutor } from '@/api/tutor'
import { formatDateTime, maskMobile } from '@/utils/format'

const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, keyword: '', auditStatus: undefined as number|undefined })
const dialogVisible = ref(false)
const submitting = ref(false)
const auditForm = reactive({ tutorId: 0, status: 1, remark: '' })

async function loadData() {
  loading.value = true
  try {
    const res = await getTutorAuditList(query)
    tableData.value = (res.data as any).list
    total.value = (res.data as any).total
  } finally { loading.value = false }
}

function openAudit(row: any, status: number) {
  auditForm.tutorId = row.id
  auditForm.status = status
  auditForm.remark = ''
  dialogVisible.value = true
}

async function submitAudit() {
  submitting.value = true
  try {
    await auditTutor(auditForm.tutorId, { status: auditForm.status, remark: auditForm.remark })
    ElMessage.success('审核完成')
    dialogVisible.value = false
    loadData()
  } finally { submitting.value = false }
}

onMounted(loadData)
</script>

<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
.text-gray { color: #c0c4cc; font-size: 13px; }
</style>
