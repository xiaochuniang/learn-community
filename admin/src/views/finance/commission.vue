<template>
  <div class="page-card">
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="orderId" label="订单ID" width="100" />
      <el-table-column prop="tutorId" label="陪伴师ID" width="100" />
      <el-table-column prop="orderAmount" label="订单金额" width="110" />
      <el-table-column prop="tutorIncome" label="师傅收入" width="110" />
      <el-table-column prop="platformIncome" label="平台收入" width="110" />
      <el-table-column label="结算状态" width="100">
        <template #default="{ row }">
          <el-tag :type="['warning','success','danger'][row.status]" size="small">
            {{ ['待结算','已结算','已冻结'][row.status] }}
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
import { getCommissionList } from '@/api/order'
import { formatDateTime } from '@/utils/format'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const pageSize = 10
async function loadData() {
  loading.value = true
  try { const res = await getCommissionList({ page: page.value, pageSize }); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
onMounted(loadData)
</script>
<style scoped>.pagination { margin-top: 16px; justify-content: flex-end; }</style>
