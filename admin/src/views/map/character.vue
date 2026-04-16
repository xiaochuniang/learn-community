<template>
  <div class="character-page">
    <div class="page-card">
      <div class="card-header">
        <span class="card-title">🎭 卡通人物配置</span>
        <span class="card-subtitle">配置地图中卡通角色的外观与行为</span>
      </div>

      <div class="char-layout" v-loading="loading">
        <!-- 左：预览 -->
        <div class="char-preview">
          <div class="preview-title">外观预览</div>
          <div class="preview-box">
            <!-- SVG Q版人物预览 -->
            <svg width="120" height="160" viewBox="0 0 120 160">
              <!-- 腿部 -->
              <rect x="45" y="105" width="14" height="28" rx="4"
                    :fill="form.body_color" />
              <rect x="61" y="105" width="14" height="28" rx="4"
                    :fill="form.body_color" />
              <!-- 身体 -->
              <rect x="38" y="72" width="44" height="38" rx="8"
                    :fill="form.body_color" />
              <!-- 手臂 -->
              <rect x="22" y="76" width="16" height="26" rx="6"
                    :fill="form.skin_color" />
              <rect x="82" y="76" width="16" height="26" rx="6"
                    :fill="form.skin_color" />
              <!-- 头部 -->
              <circle cx="60" cy="55" r="24" :fill="form.skin_color" />
              <!-- 头发 -->
              <ellipse cx="60" cy="36" rx="22" ry="12" :fill="form.hair_color" />
              <rect x="38" y="36" width="44" height="14" :fill="form.hair_color" />
              <!-- 眼睛 -->
              <circle cx="52" cy="56" r="4" fill="#333" />
              <circle cx="68" cy="56" r="4" fill="#333" />
              <circle cx="53" cy="55" r="1.5" fill="#fff" />
              <circle cx="69" cy="55" r="1.5" fill="#fff" />
              <!-- 嘴巴 -->
              <path d="M 53 67 Q 60 73 67 67" stroke="#555" stroke-width="2.5"
                    fill="none" stroke-linecap="round" />
              <!-- 名字 -->
              <text x="60" y="148" text-anchor="middle" font-size="13"
                    font-weight="bold" fill="#1B5E20">{{ form.name }}</text>
            </svg>
          </div>
          <div class="preview-tip">实际效果以小程序渲染为准</div>
        </div>

        <!-- 右：表单 -->
        <div class="char-form">
          <el-form ref="formRef" :model="form" label-width="110px">

            <el-form-item label="角色名称"
              :rules="[{ required: true, message: '请输入名称' }]"
              prop="name">
              <el-input v-model="form.name" placeholder="如：小明" style="max-width:200px" />
            </el-form-item>

            <el-divider content-position="left">外观颜色</el-divider>

            <el-form-item label="衣服颜色">
              <div class="color-row">
                <el-color-picker v-model="form.body_color" show-alpha />
                <el-input v-model="form.body_color" style="width:110px" />
              </div>
            </el-form-item>

            <el-form-item label="皮肤颜色">
              <div class="color-row">
                <el-color-picker v-model="form.skin_color" show-alpha />
                <el-input v-model="form.skin_color" style="width:110px" />
              </div>
            </el-form-item>

            <el-form-item label="头发颜色">
              <div class="color-row">
                <el-color-picker v-model="form.hair_color" show-alpha />
                <el-input v-model="form.hair_color" style="width:110px" />
              </div>
            </el-form-item>

            <el-divider content-position="left">行为设置</el-divider>

            <el-form-item label="行走速度">
              <el-slider v-model="form.walk_speed"
                :min="0.5" :max="6" :step="0.5" show-stops
                style="width:240px" />
              <span style="margin-left:12px;color:#666">{{ form.walk_speed }} 格/秒</span>
            </el-form-item>

            <el-form-item label="初始位置 X">
              <el-input-number v-model="form.home_grid_x" :min="0" :max="20" />
              <span style="margin-left:8px;color:#999;font-size:12px">对应地图网格坐标</span>
            </el-form-item>

            <el-form-item label="初始位置 Y">
              <el-input-number v-model="form.home_grid_y" :min="0" :max="20" />
            </el-form-item>

            <el-form-item>
              <el-button type="primary" :loading="saving" @click="handleSave">
                保存配置
              </el-button>
              <el-button @click="loadData">重置</el-button>
            </el-form-item>
          </el-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getAdminCharacter, saveCharacterConfig, type CharacterConfigItem } from '@/api/map'

const loading = ref(false)
const saving  = ref(false)
const formRef = ref()

const form = reactive<Partial<CharacterConfigItem>>({
  name:        '小明',
  body_color:  '#FF9800',
  skin_color:  '#FFCC80',
  hair_color:  '#5D4037',
  walk_speed:  2,
  home_grid_x: 2,
  home_grid_y: 8,
})

async function loadData() {
  loading.value = true
  try {
    const res = await getAdminCharacter()
    Object.assign(form, res.data)
  } catch {
    // use defaults
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  await formRef.value?.validate()
  saving.value = true
  try {
    await saveCharacterConfig({ ...form })
    ElMessage.success('配置保存成功')
  } finally {
    saving.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.character-page { padding: 20px; }

.page-card {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.card-header { margin-bottom: 24px; }
.card-title  { font-size: 18px; font-weight: 700; color: #1a1a2e; margin-right: 12px; }
.card-subtitle { font-size: 13px; color: #999; }

.char-layout {
  display: flex;
  gap: 48px;
  align-items: flex-start;
}

.char-preview {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.preview-title { font-size: 14px; font-weight: 600; color: #606266; }

.preview-box {
  background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
  border-radius: 20px;
  padding: 24px;
  border: 2px dashed #A5D6A7;
}

.preview-tip { font-size: 12px; color: #aaa; }

.char-form { flex: 1; }

.color-row {
  display: flex;
  align-items: center;
  gap: 12px;
}
</style>
