<template>
  <div class="page-card">
    <div class="search-bar">
      <el-button type="primary" :icon="Plus" @click="openDialog()">新增自习室</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="名称" min-width="120" />
      <el-table-column label="类型" width="120">
        <template #default="{ row }">{{ ['','公共自习','专注计时','陪伴自习'][row.roomType] }}</template>
      </el-table-column>
      <el-table-column prop="capacity" label="容量" width="80" />
      <el-table-column prop="onlineCount" label="在线人数" width="100" />
      <el-table-column label="收费" width="80">
        <template #default="{ row }">{{ row.isFree ? '免费' : row.price + '元/h' }}</template>
      </el-table-column>
      <el-table-column label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">{{ row.status === 1 ? '开放' : '关闭' }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="120" fixed="right">
        <template #default="{ row }">
          <el-button size="small" link type="primary" @click="openDialog(row)">编辑</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog v-model="dialogVisible" :title="editRow ? '编辑自习室' : '新增自习室'" width="500px">
      <el-form :model="form" label-width="90px">
        <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="类型">
          <el-select v-model="form.roomType">
            <el-option label="公共自习" :value="1" /><el-option label="专注计时" :value="2" /><el-option label="陪伴自习" :value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="容量"><el-input-number v-model="form.capacity" :min="1" /></el-form-item>
        <el-form-item label="是否免费"><el-switch v-model="form.isFree" :active-value="1" :inactive-value="0" /></el-form-item>
        <el-form-item label="价格(元/h)" v-if="!form.isFree"><el-input-number v-model="form.price" :min="0" :precision="2" /></el-form-item>
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
import { Plus } from '@element-plus/icons-vue'
import request from '@/utils/request'
const loading = ref(false)
const tableData = ref<any[]>([])
const dialogVisible = ref(false)
const submitting = ref(false)
const editRow = ref<any>(null)
const form = reactive({ name: '', roomType: 1, capacity: 100, isFree: 1, price: 0, status: 1 })
async function loadData() {
  loading.value = true
  try { const res = await request.get('/admin/study/room/list'); tableData.value = (res.data as any).list || [] }
  finally { loading.value = false }
}
function openDialog(row?: any) {
  editRow.value = row || null
  Object.assign(form, row || { name: '', roomType: 1, capacity: 100, isFree: 1, price: 0, status: 1 })
  dialogVisible.value = true
}
async function submitForm() {
  submitting.value = true
  try {
    if (editRow.value) { await request.put(`/admin/study/room/${editRow.value.id}`, form) }
    else { await request.post('/admin/study/room', form) }
    ElMessage.success('保存成功'); dialogVisible.value = false; loadData()
  } finally { submitting.value = false }
}
onMounted(loadData)
</script>
<style scoped>.search-bar { margin-bottom: 16px; }</style>
