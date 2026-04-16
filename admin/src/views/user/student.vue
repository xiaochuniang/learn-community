<template>
  <div class="page-card">
    <!-- 搜索栏 -->
    <div class="search-bar">
      <el-input v-model="query.keyword" placeholder="搜索昵称/手机号" clearable style="width:200px" @keyup.enter="loadData" />
      <el-select v-model="query.status" placeholder="状态" clearable style="width:120px">
        <el-option label="正常" :value="1" />
        <el-option label="禁用" :value="0" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
      <el-button @click="resetQuery">重置</el-button>
    </div>

    <!-- 数据表格 -->
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column label="头像" width="70">
        <template #default="{ row }">
          <el-avatar :size="36" :src="row.avatar" icon="UserFilled" />
        </template>
      </el-table-column>
      <el-table-column prop="nickname" label="昵称" min-width="120" show-overflow-tooltip />
      <el-table-column prop="realName" label="真实姓名" width="100" />
      <el-table-column label="手机号" width="140">
        <template #default="{ row }">{{ maskMobile(row.mobile) }}</template>
      </el-table-column>
      <el-table-column prop="school" label="学校" min-width="120" show-overflow-tooltip />
      <el-table-column prop="grade" label="年级" width="80" />
      <el-table-column label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">
            {{ row.status === 1 ? '正常' : '禁用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="累计学习" width="100">
        <template #default="{ row }">{{ formatStudyTime(row.totalStudyMin) }}</template>
      </el-table-column>
      <el-table-column label="注册时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
      <el-table-column label="操作" width="140" fixed="right">
        <template #default="{ row }">
          <el-button
            :type="row.status === 1 ? 'danger' : 'success'"
            size="small"
            link
            @click="toggleStatus(row)"
          >
            {{ row.status === 1 ? '禁用' : '启用' }}
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- 分页 -->
    <el-pagination
      v-model:current-page="query.page"
      v-model:page-size="query.pageSize"
      :total="total"
      :page-sizes="[10, 20, 50]"
      layout="total, sizes, prev, pager, next"
      class="pagination"
      @change="loadData"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import { getUserList, updateUserStatus, type UserItem } from '@/api/user'
import { formatDateTime, formatStudyTime, maskMobile } from '@/utils/format'

const loading = ref(false)
const tableData = ref<UserItem[]>([])
const total = ref(0)

const query = reactive({ page: 1, pageSize: 10, keyword: '', status: undefined as number | undefined, userType: 1 })

async function loadData() {
  loading.value = true
  try {
    const res = await getUserList(query)
    tableData.value = res.data.list
    total.value = res.data.total
  } finally {
    loading.value = false
  }
}

function resetQuery() {
  query.keyword = ''
  query.status = undefined
  query.page = 1
  loadData()
}

async function toggleStatus(row: UserItem) {
  const newStatus = row.status === 1 ? 0 : 1
  await ElMessageBox.confirm(`确定要${newStatus === 0 ? '禁用' : '启用'}该用户吗？`, '提示', { type: 'warning' })
  await updateUserStatus(row.id, newStatus)
  ElMessage.success('操作成功')
  loadData()
}

onMounted(loadData)
</script>

<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
