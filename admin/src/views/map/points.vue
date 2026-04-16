<template>
  <div class="map-points-page">
    <!-- 操作栏 -->
    <div class="page-card" style="margin-bottom:0; padding-bottom:0">
      <div class="toolbar">
        <span class="toolbar-title">🗺️ 虚拟地图点位管理</span>
        <el-button type="primary" :icon="Plus" @click="openAdd">新增点位</el-button>
      </div>
    </div>

    <!-- 表格 -->
    <div class="page-card">
      <el-table v-loading="loading" :data="list" border stripe>
        <el-table-column prop="id"     label="ID"   width="64" align="center" />
        <el-table-column prop="icon"   label="图标" width="64" align="center">
          <template #default="{ row }">
            <span style="font-size:24px">{{ row.icon }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="name"  label="名称"   width="100" />
        <el-table-column prop="key"   label="标识"   width="120" show-overflow-tooltip />
        <el-table-column label="坐标(X,Y)" width="110" align="center">
          <template #default="{ row }">({{ row.grid_x }}, {{ row.grid_y }})</template>
        </el-table-column>
        <el-table-column prop="build_height" label="楼高" width="70" align="center" />
        <el-table-column label="颜色预览" width="110" align="center">
          <template #default="{ row }">
            <div style="display:flex;gap:4px;justify-content:center">
              <span :style="{ width:'16px',height:'16px',borderRadius:'3px',background:row.color_top,display:'inline-block' }" />
              <span :style="{ width:'16px',height:'16px',borderRadius:'3px',background:row.color_left,display:'inline-block' }" />
              <span :style="{ width:'16px',height:'16px',borderRadius:'3px',background:row.color_right,display:'inline-block' }" />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="target_path" label="跳转路径" min-width="180" show-overflow-tooltip />
        <el-table-column prop="sort"  label="排序" width="70" align="center" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">
              {{ row.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="是否初始" width="80" align="center">
          <template #default="{ row }">
            <el-tag v-if="row.is_home === 1" type="warning" size="small">家</el-tag>
            <span v-else style="color:#aaa">—</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="140" align="center" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="openEdit(row)">编辑</el-button>
            <el-button size="small" type="danger" :disabled="row.is_home === 1" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div style="margin-top:16px;text-align:right">
        <el-pagination
          v-model:current-page="page"
          v-model:page-size="limit"
          :total="total"
          layout="total, prev, pager, next"
          @change="loadList"
        />
      </div>
    </div>

    <!-- 新增/编辑弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="form.id ? '编辑点位' : '新增点位'"
      width="680px"
      destroy-on-close
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px" label-position="right">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 24px">
          <el-form-item label="点位标识" prop="key">
            <el-input v-model="form.key" placeholder="英文唯一标识，如 library" :disabled="!!form.id" />
          </el-form-item>
          <el-form-item label="点位名称" prop="name">
            <el-input v-model="form.name" placeholder="如：图书馆" />
          </el-form-item>
          <el-form-item label="图标(Emoji)" prop="icon">
            <el-input v-model="form.icon" placeholder="如：📚" style="width:120px" />
          </el-form-item>
          <el-form-item label="排序权重">
            <el-input-number v-model="form.sort" :min="0" :max="999" />
          </el-form-item>
          <el-form-item label="网格 X" prop="grid_x">
            <el-input-number v-model="form.grid_x" :min="0" :max="20" />
          </el-form-item>
          <el-form-item label="网格 Y" prop="grid_y">
            <el-input-number v-model="form.grid_y" :min="0" :max="20" />
          </el-form-item>
          <el-form-item label="建筑高度">
            <el-input-number v-model="form.build_height" :min="20" :max="120" :step="4" />
          </el-form-item>
          <el-form-item label="状态">
            <el-select v-model="form.status" style="width:100%">
              <el-option label="启用" :value="1" />
              <el-option label="禁用" :value="0" />
            </el-select>
          </el-form-item>
        </div>

        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="2" placeholder="点位功能描述" />
        </el-form-item>
        <el-form-item label="跳转路径">
          <el-input v-model="form.target_path" placeholder="小程序页面路径，如 /pages/map/library" />
        </el-form-item>

        <!-- 颜色配置 -->
        <el-form-item label="颜色预览">
          <div style="display:flex;gap:8px;align-items:center">
            <div :style="buildingPreviewStyle" style="width:60px;height:50px;border-radius:6px" />
            <span style="color:#999;font-size:12px">根据三面颜色实时预览（近似）</span>
          </div>
        </el-form-item>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 24px">
          <el-form-item label="顶面颜色" label-width="80px">
            <div style="display:flex;gap:8px;align-items:center">
              <el-color-picker v-model="form.color_top" size="small" />
              <el-input v-model="form.color_top" size="small" style="width:90px" />
            </div>
          </el-form-item>
          <el-form-item label="左面颜色" label-width="80px">
            <div style="display:flex;gap:8px;align-items:center">
              <el-color-picker v-model="form.color_left" size="small" />
              <el-input v-model="form.color_left" size="small" style="width:90px" />
            </div>
          </el-form-item>
          <el-form-item label="右面颜色" label-width="80px">
            <div style="display:flex;gap:8px;align-items:center">
              <el-color-picker v-model="form.color_right" size="small" />
              <el-input v-model="form.color_right" size="small" style="width:90px" />
            </div>
          </el-form-item>
        </div>

        <el-form-item label="是否为家">
          <el-switch v-model="form.is_home" :active-value="1" :inactive-value="0" />
          <span style="margin-left:8px;font-size:12px;color:#999">每张地图建议只有一个"家"作为初始位置</span>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="handleSave">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getAdminMapPoints, saveMapPoint, deleteMapPoint, type MapPointItem } from '@/api/map'

// ── 列表数据 ──────────────────────────────────────────────────────────
const loading = ref(false)
const list    = ref<MapPointItem[]>([])
const total   = ref(0)
const page    = ref(1)
const limit   = ref(15)

async function loadList() {
  loading.value = true
  try {
    const res = await getAdminMapPoints({ page: page.value, limit: limit.value })
    list.value  = (res.data as any).list  ?? []
    total.value = (res.data as any).total ?? 0
  } finally {
    loading.value = false
  }
}

// ── 表单 ──────────────────────────────────────────────────────────────
const dialogVisible = ref(false)
const saving        = ref(false)
const formRef       = ref()

const emptyForm = (): Partial<MapPointItem> => ({
  id: undefined, key: '', name: '', icon: '📍', description: '',
  grid_x: 5, grid_y: 5, build_height: 48,
  color_top: '#87CEEB', color_left: '#5BA3D0', color_right: '#3A7DB8',
  target_path: '', is_home: 0, sort: 0, status: 1,
})

const form = reactive<Partial<MapPointItem>>(emptyForm())

const rules = {
  key:    [{ required: true, message: '请输入标识', trigger: 'blur' }],
  name:   [{ required: true, message: '请输入名称', trigger: 'blur' }],
  grid_x: [{ required: true, message: '请输入X坐标', trigger: 'blur' }],
  grid_y: [{ required: true, message: '请输入Y坐标', trigger: 'blur' }],
}

/** 建筑颜色三面预览（简单渐变） */
const buildingPreviewStyle = computed(() => ({
  background: `linear-gradient(135deg, ${form.color_top ?? '#87CEEB'} 0%, ${form.color_right ?? '#3A7DB8'} 100%)`,
}))

function openAdd() {
  Object.assign(form, emptyForm())
  dialogVisible.value = true
}

function openEdit(row: MapPointItem) {
  Object.assign(form, { ...row })
  dialogVisible.value = true
}

async function handleSave() {
  await formRef.value?.validate()
  saving.value = true
  try {
    await saveMapPoint({ ...form })
    ElMessage.success(form.id ? '更新成功' : '新增成功')
    dialogVisible.value = false
    await loadList()
  } finally {
    saving.value = false
  }
}

async function handleDelete(row: MapPointItem) {
  await ElMessageBox.confirm(`确定删除点位「${row.name}」吗？`, '确认删除', {
    confirmButtonText: '删除', cancelButtonText: '取消', type: 'warning',
  })
  await deleteMapPoint(row.id)
  ElMessage.success('删除成功')
  await loadList()
}

onMounted(loadList)
</script>

<style scoped>
.map-points-page { padding: 20px; }

.page-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 16px;
}

.toolbar-title {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
}
</style>
