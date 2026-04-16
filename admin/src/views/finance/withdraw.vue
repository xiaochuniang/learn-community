<template>
  <div class="page-card">
    <div class="search-bar">
      <el-select v-model="query.status" placeholder="提现状态" clearable style="width:130px">
        <el-option label="待审核" :value="0" /><el-option label="处理中" :value="1" />
        <el-option label="已完成" :value="2" /><el-option label="已拒绝" :value="3" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="tutorId" label="陪伴师ID" width="100" />
      <el-table-column prop="amount" label="申请金额" width="110" />
      <el-table-column prop="actualAmount" label="到账金额" width="110" />
      <el-table-column prop="realName" label="收款人" width="100" />
      <el-table-column label="状态" width="100">
        <template #default="{ row }">
          <el-tag :type="['warning','primary','success','danger'][row.status]" size="small">
            {{ ['待审核','处理中','已完成','已拒绝'][row.status] }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="申请时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
      <el-table-column label="操作" width="140" fixed="right">
        <template #default="{ row }">
          <template v-if="row.status === 0">
            <el-button size="small" type="success" link @click="doAudit(row, 2)">通过</el-button>
            <el-button size="small" type="danger" link @click="doAudit(row, 3)">拒绝</el-button>
          </template>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination v-model:current-page="query.page" v-model:page-size="query.pageSize" :total="total"
      :page-sizes="[10,20,50]" layout="total, sizes, prev, pager, next" class="pagination" @change="loadData" />
  </div>
</template>
<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import { getWithdrawList, auditWithdraw } from '@/api/order'
import { formatDateTime } from '@/utils/format'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, status: undefined as number|undefined })
async function loadData() {
  loading.value = true
  try { const res = await getWithdrawList(query); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
async function doAudit(row: any, status: number) {
  await auditWithdraw(row.id, { status, remark: '' }); ElMessage.success('操作成功'); loadData()
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { display: flex; gap: 8px; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
