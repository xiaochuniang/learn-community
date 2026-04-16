<template>
  <div class="page-card">
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="parentId" label="家长ID" width="100" />
      <el-table-column prop="studentId" label="学生ID" width="100" />
      <el-table-column label="绑定状态" width="100">
        <template #default="{ row }">
          <el-tag :type="['warning','success','info'][row.bindStatus]" size="small">
            {{ ['待确认','已绑定','已解绑'][row.bindStatus] }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="绑定时间" width="160">
        <template #default="{ row }">{{ row.bindTime || '-' }}</template>
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
  try { const res = await request.get('/admin/guardian/list', { page: page.value, pageSize }); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
onMounted(loadData)
</script>
<style scoped>.pagination { margin-top: 16px; justify-content: flex-end; }</style>
