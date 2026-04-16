<template>
  <div class="page-card">
    <div class="search-bar">
      <el-button type="primary" :icon="Plus" @click="openDialog()">新增话题</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="话题名称" min-width="120" />
      <el-table-column prop="description" label="描述" min-width="160" show-overflow-tooltip />
      <el-table-column prop="postCount" label="帖子数" width="90" />
      <el-table-column prop="sort" label="排序" width="80" />
      <el-table-column label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'success' : 'info'" size="small">{{ row.status === 1 ? '显示' : '隐藏' }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="120" fixed="right">
        <template #default="{ row }">
          <el-button size="small" link type="primary" @click="openDialog(row)">编辑</el-button>
          <el-button size="small" link type="danger" @click="doDelete(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog v-model="dialogVisible" :title="editRow ? '编辑话题' : '新增话题'" width="480px">
      <el-form :model="form" label-width="80px">
        <el-form-item label="话题名称"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="描述"><el-input v-model="form.description" type="textarea" :rows="2" /></el-form-item>
        <el-form-item label="排序权重"><el-input-number v-model="form.sort" :min="0" /></el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="form.status" :active-value="1" :inactive-value="0" />
        </el-form-item>
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
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getTopicList, createTopic, updateTopic, deleteTopic } from '@/api/community'

const loading = ref(false)
const tableData = ref<any[]>([])
const dialogVisible = ref(false)
const submitting = ref(false)
const editRow = ref<any>(null)
const form = reactive({ name: '', description: '', sort: 0, status: 1 })

async function loadData() {
  loading.value = true
  try { const res = await getTopicList(); tableData.value = (res.data as any).list || res.data as any }
  finally { loading.value = false }
}
function openDialog(row?: any) {
  editRow.value = row || null
  Object.assign(form, row ? { name: row.name, description: row.description, sort: row.sort, status: row.status } : { name: '', description: '', sort: 0, status: 1 })
  dialogVisible.value = true
}
async function submitForm() {
  submitting.value = true
  try {
    if (editRow.value) { await updateTopic(editRow.value.id, form) } else { await createTopic(form) }
    ElMessage.success('保存成功'); dialogVisible.value = false; loadData()
  } finally { submitting.value = false }
}
async function doDelete(row: any) {
  await ElMessageBox.confirm('确定删除该话题？', '提示', { type: 'warning' })
  await deleteTopic(row.id); ElMessage.success('删除成功'); loadData()
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { margin-bottom: 16px; }
</style>
