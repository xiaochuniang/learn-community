<template>
  <div class="page-card">
    <div class="search-bar">
      <el-input v-model="query.keyword" placeholder="搜索账号/姓名" clearable style="width:200px" @keyup.enter="loadData" />
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
      <el-button :icon="Plus" @click="openDialog()">新增管理员</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="username" label="账号" width="140" />
      <el-table-column prop="realName" label="姓名" width="100" />
      <el-table-column prop="mobile" label="手机号" width="140" />
      <el-table-column prop="roleName" label="角色" width="120" />
      <el-table-column label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">{{ row.status === 1 ? '启用' : '禁用' }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="最后登录" width="160">
        <template #default="{ row }">{{ row.lastLoginAt || '-' }}</template>
      </el-table-column>
      <el-table-column label="操作" width="140" fixed="right">
        <template #default="{ row }">
          <el-button size="small" link type="primary" @click="openDialog(row)">编辑</el-button>
          <el-button size="small" link :type="row.status === 1 ? 'danger':'success'" @click="toggleStatus(row)">
            {{ row.status === 1 ? '禁用' : '启用' }}
          </el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination v-model:current-page="query.page" v-model:page-size="query.pageSize" :total="total"
      :page-sizes="[10,20,50]" layout="total, sizes, prev, pager, next" class="pagination" @change="loadData" />

    <el-dialog v-model="dialogVisible" :title="editRow ? '编辑管理员' : '新增管理员'" width="480px">
      <el-form :model="form" label-width="80px">
        <el-form-item label="账号"><el-input v-model="form.username" :disabled="!!editRow" /></el-form-item>
        <el-form-item label="姓名"><el-input v-model="form.realName" /></el-form-item>
        <el-form-item label="手机号"><el-input v-model="form.mobile" /></el-form-item>
        <el-form-item v-if="!editRow" label="密码"><el-input v-model="form.password" type="password" show-password /></el-form-item>
        <el-form-item label="状态"><el-switch v-model="form.status" :active-value="1" :inactive-value="0" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="submitForm">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>
<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Plus } from '@element-plus/icons-vue'
import request from '@/utils/request'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, keyword: '' })
const dialogVisible = ref(false)
const submitting = ref(false)
const editRow = ref<any>(null)
const form = reactive({ username: '', realName: '', mobile: '', password: '', status: 1 })
async function loadData() {
  loading.value = true
  try { const res = await request.get('/admin/system/admin/list', query); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
function openDialog(row?: any) {
  editRow.value = row || null
  Object.assign(form, row ? { username: row.username, realName: row.realName, mobile: row.mobile, password: '', status: row.status } : { username: '', realName: '', mobile: '', password: '', status: 1 })
  dialogVisible.value = true
}
async function submitForm() {
  submitting.value = true
  try {
    if (editRow.value) { await request.put(`/admin/system/admin/${editRow.value.id}`, form) }
    else { await request.post('/admin/system/admin', form) }
    ElMessage.success('保存成功'); dialogVisible.value = false; loadData()
  } finally { submitting.value = false }
}
async function toggleStatus(row: any) {
  await request.put(`/admin/system/admin/${row.id}/status`, { status: row.status === 1 ? 0 : 1 })
  ElMessage.success('操作成功'); loadData()
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
