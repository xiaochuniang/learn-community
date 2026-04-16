<template>
  <div class="page-card">
    <div class="search-bar">
      <el-input v-model="query.keyword" placeholder="搜索姓名/手机号" clearable style="width:200px" @keyup.enter="loadData" />
      <el-select v-model="query.auditStatus" placeholder="审核状态" clearable style="width:130px">
        <el-option label="待审核" :value="0" />
        <el-option label="已通过" :value="1" />
        <el-option label="已拒绝" :value="2" />
      </el-select>
      <el-select v-model="query.status" placeholder="上线状态" clearable style="width:120px">
        <el-option label="上线" :value="1" />
        <el-option label="下线" :value="0" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
      <el-button @click="resetQuery">重置</el-button>
    </div>

    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column label="头像" width="70">
        <template #default="{ row }">
          <el-avatar :size="36" :src="row.avatar" icon="UserFilled" />
        </template>
      </el-table-column>
      <el-table-column prop="realName" label="姓名" width="100" />
      <el-table-column label="手机号" width="140">
        <template #default="{ row }">{{ maskMobile(row.mobile) }}</template>
      </el-table-column>
      <el-table-column prop="school" label="院校" min-width="130" show-overflow-tooltip />
      <el-table-column prop="subjects" label="科目" min-width="120" show-overflow-tooltip />
      <el-table-column prop="servicePrice" label="单价(元/h)" width="110" />
      <el-table-column prop="score" label="评分" width="80" />
      <el-table-column prop="orderCount" label="接单数" width="80" />
      <el-table-column label="审核状态" width="90">
        <template #default="{ row }">
          <el-tag :type="['warning','success','danger'][row.auditStatus]" size="small">
            {{ ['待审核','已通过','已拒绝'][row.auditStatus] }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="上线状态" width="90">
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'success' : 'info'" size="small">
            {{ row.status === 1 ? '上线' : '下线' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="注册时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
      <el-table-column label="操作" width="120" fixed="right">
        <template #default="{ row }">
          <el-button size="small" link type="primary" @click="viewDetail(row)">详情</el-button>
          <el-button size="small" link :type="row.status === 1 ? 'danger':'success'" @click="toggleOnline(row)">
            {{ row.status === 1 ? '下线' : '上线' }}
          </el-button>
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
import { getTutorList, updateTutorStatus } from '@/api/tutor'
import { formatDateTime, maskMobile } from '@/utils/format'

const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, keyword: '', auditStatus: undefined as number|undefined, status: undefined as number|undefined })

async function loadData() {
  loading.value = true
  try {
    const res = await getTutorList(query)
    tableData.value = (res.data as any).list
    total.value = (res.data as any).total
  } finally { loading.value = false }
}

function resetQuery() { query.keyword = ''; query.auditStatus = undefined; query.status = undefined; query.page = 1; loadData() }

function viewDetail(_row: any) { /* TODO: 跳转详情页 */ }

async function toggleOnline(row: any) {
  await updateTutorStatus(row.id, row.status === 1 ? 0 : 1)
  ElMessage.success('操作成功')
  loadData()
}

onMounted(loadData)
</script>

<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
