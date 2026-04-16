<template>
  <div class="page-card">
    <div class="search-bar">
      <el-input v-model="query.keyword" placeholder="搜索用户ID/科目" clearable style="width:200px" @keyup.enter="loadData" />
      <el-date-picker v-model="query.checkDate" type="date" placeholder="选择日期" value-format="YYYY-MM-DD" style="width:160px" />
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="userId" label="用户ID" width="90" />
      <el-table-column prop="checkDate" label="打卡日期" width="120" />
      <el-table-column prop="subject" label="科目" width="100" />
      <el-table-column prop="studyMinutes" label="学习时长(min)" width="130" />
      <el-table-column prop="consecutiveDays" label="连续天数" width="100" />
      <el-table-column prop="content" label="内容" min-width="160" show-overflow-tooltip />
      <el-table-column label="打卡时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
    </el-table>
    <el-pagination v-model:current-page="query.page" v-model:page-size="query.pageSize" :total="total"
      :page-sizes="[10,20,50]" layout="total, sizes, prev, pager, next" class="pagination" @change="loadData" />
  </div>
</template>
<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { Search } from '@element-plus/icons-vue'
import request from '@/utils/request'
import { formatDateTime } from '@/utils/format'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, keyword: '', checkDate: '' })
async function loadData() {
  loading.value = true
  try { const res = await request.get('/admin/checkin/list', query); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
