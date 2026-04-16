<template>
  <div class="page-card">
    <div class="search-bar">
      <el-select v-model="query.status" placeholder="封禁状态" clearable style="width:130px">
        <el-option label="生效中" :value="1" /><el-option label="已解封" :value="0" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="userId" label="用户ID" width="100" />
      <el-table-column label="封禁类型" width="110">
        <template #default="{ row }">{{ ['','禁止发言','禁止登录','永久封禁'][row.banType] }}</template>
      </el-table-column>
      <el-table-column prop="reason" label="原因" min-width="160" show-overflow-tooltip />
      <el-table-column prop="startTime" label="开始时间" width="160" />
      <el-table-column label="结束时间" width="160">
        <template #default="{ row }">{{ row.endTime || '永久' }}</template>
      </el-table-column>
      <el-table-column label="状态" width="90">
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'danger' : 'success'" size="small">{{ row.status === 1 ? '生效中' : '已解封' }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="100" fixed="right">
        <template #default="{ row }">
          <el-button v-if="row.status === 1" size="small" type="success" link @click="doUnban(row)">解封</el-button>
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
import { getBanList, unbanUser } from '@/api/risk'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, status: undefined as number|undefined })
async function loadData() {
  loading.value = true
  try { const res = await getBanList(query); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
async function doUnban(row: any) {
  await unbanUser(row.id); ElMessage.success('解封成功'); loadData()
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { display: flex; gap: 8px; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
