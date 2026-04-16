<template>
  <div class="page-card">
    <div class="search-bar">
      <el-select v-model="query.riskType" placeholder="风险类型" clearable style="width:130px">
        <el-option label="违禁词" :value="1" /><el-option label="违规图片" :value="2" />
        <el-option label="骚扰" :value="3" /><el-option label="广告" :value="4" />
      </el-select>
      <el-select v-model="query.status" placeholder="处理状态" clearable style="width:130px">
        <el-option label="待处理" :value="0" /><el-option label="已处理" :value="1" /><el-option label="误报" :value="2" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="userId" label="违规用户ID" width="110" />
      <el-table-column label="内容类型" width="100">
        <template #default="{ row }">{{ ['','帖子','评论','打卡','用户信息','师傅信息'][row.targetType] }}</template>
      </el-table-column>
      <el-table-column label="风险类型" width="100">
        <template #default="{ row }">{{ ['','违禁词','违规图片','骚扰','广告','其他'][row.riskType] }}</template>
      </el-table-column>
      <el-table-column prop="riskContent" label="违规内容" min-width="160" show-overflow-tooltip />
      <el-table-column label="来源" width="100">
        <template #default="{ row }">{{ ['','自动识别','用户举报','人工审核'][row.source] }}</template>
      </el-table-column>
      <el-table-column label="状态" width="90">
        <template #default="{ row }">
          <el-tag :type="['warning','success','info'][row.status]" size="small">{{ ['待处理','已处理','误报'][row.status] }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="记录时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
      <el-table-column label="操作" width="120" fixed="right">
        <template #default="{ row }">
          <el-button v-if="row.status === 0" size="small" type="primary" link @click="openHandle(row)">处理</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination v-model:current-page="query.page" v-model:page-size="query.pageSize" :total="total"
      :page-sizes="[10,20,50]" layout="total, sizes, prev, pager, next" class="pagination" @change="loadData" />

    <el-dialog v-model="dialogVisible" title="处理违规" width="480px">
      <el-form :model="handleForm" label-width="90px">
        <el-form-item label="处理方式">
          <el-select v-model="handleForm.handleType" style="width:100%">
            <el-option label="警告" :value="1" /><el-option label="删除内容" :value="2" />
            <el-option label="封禁账号" :value="3" /><el-option label="封禁+删内容" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="处理备注">
          <el-input v-model="handleForm.handleRemark" type="textarea" :rows="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="submitHandle">确认处理</el-button>
      </template>
    </el-dialog>
  </div>
</template>
<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import { getRiskList, handleRisk } from '@/api/risk'
import { formatDateTime } from '@/utils/format'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, riskType: undefined as number|undefined, status: undefined as number|undefined })
const dialogVisible = ref(false)
const submitting = ref(false)
const handleForm = reactive({ riskId: 0, handleType: 1, handleRemark: '' })
async function loadData() {
  loading.value = true
  try { const res = await getRiskList(query); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
function openHandle(row: any) { handleForm.riskId = row.id; handleForm.handleType = 1; handleForm.handleRemark = ''; dialogVisible.value = true }
async function submitHandle() {
  submitting.value = true
  try {
    await handleRisk(handleForm.riskId, { handleType: handleForm.handleType, handleRemark: handleForm.handleRemark })
    ElMessage.success('处理成功'); dialogVisible.value = false; loadData()
  } finally { submitting.value = false }
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
