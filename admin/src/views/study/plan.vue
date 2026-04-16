<template>
  <div class="page-card">
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="userId" label="用户ID" width="90" />
      <el-table-column prop="title" label="计划标题" min-width="160" show-overflow-tooltip />
      <el-table-column prop="subject" label="科目" width="100" />
      <el-table-column prop="startDate" label="开始日期" width="120" />
      <el-table-column prop="endDate" label="结束日期" width="120" />
      <el-table-column prop="progress" label="进度" width="80">
        <template #default="{ row }">{{ row.progress }}%</template>
      </el-table-column>
      <el-table-column label="状态" width="90">
        <template #default="{ row }">
          <el-tag :type="['','primary','success','info'][row.status]" size="small">
            {{ ['','进行中','已完成','已放弃'][row.status] }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
    </el-table>
    <el-pagination v-model:current-page="page" :page-size="pageSize" :total="total"
      layout="total, prev, pager, next" class="pagination" @current-change="loadData" />
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import request from '@/utils/request'
import { formatDateTime } from '@/utils/format'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const pageSize = 10
async function loadData() {
  loading.value = true
  try { const res = await request.get('/admin/study/plan/list', { page: page.value, pageSize }); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
onMounted(loadData)
</script>
<style scoped>.pagination { margin-top: 16px; justify-content: flex-end; }</style>
