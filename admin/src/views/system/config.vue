<template>
  <div>
    <el-tabs v-model="activeGroup" type="card">
      <el-tab-pane v-for="grp in groups" :key="grp.value" :label="grp.label" :name="grp.value">
        <div class="page-card" style="margin-top:0">
          <el-table v-loading="loading" :data="filteredList" border>
            <el-table-column prop="label" label="配置名称" width="160" />
            <el-table-column prop="configKey" label="配置键" width="200" show-overflow-tooltip />
            <el-table-column label="配置值" min-width="200">
              <template #default="{ row }">
                <el-input v-model="row.configValue" size="small" @blur="saveOne(row)" />
              </template>
            </el-table-column>
            <el-table-column prop="description" label="说明" min-width="160" show-overflow-tooltip />
          </el-table>
          <div class="save-btn">
            <el-button type="primary" :loading="saving" @click="batchSave">批量保存</el-button>
          </div>
        </div>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getConfigList, batchSaveConfig, saveConfig } from '@/api/system'
const loading = ref(false)
const saving = ref(false)
const activeGroup = ref('base')
const allList = ref<any[]>([])
const groups = [
  { label: '基础配置', value: 'base' },
  { label: '支付配置', value: 'payment' },
  { label: '风控配置', value: 'risk' }
]
const filteredList = computed(() => allList.value.filter(i => i.group === activeGroup.value))
async function loadData() {
  loading.value = true
  try { const res = await getConfigList(); allList.value = (res.data as any).list || res.data as any }
  finally { loading.value = false }
}
async function saveOne(row: any) {
  await saveConfig({ configKey: row.configKey, configValue: row.configValue })
}
async function batchSave() {
  saving.value = true
  try {
    await batchSaveConfig(filteredList.value.map(i => ({ configKey: i.configKey, configValue: i.configValue })))
    ElMessage.success('保存成功')
  } finally { saving.value = false }
}
onMounted(loadData)
</script>
<style scoped>.save-btn { margin-top: 12px; text-align: right; }</style>
