<template>
  <div class="dashboard">
    <!-- 统计卡片 -->
    <div class="stat-cards">
      <div v-for="card in statCards" :key="card.label" class="stat-card" :style="{ borderLeftColor: card.color }">
        <div class="stat-info">
          <p class="stat-label">{{ card.label }}</p>
          <p class="stat-value">{{ card.value }}</p>
          <p class="stat-desc">{{ card.desc }}</p>
        </div>
        <div class="stat-icon" :style="{ background: card.color + '22' }">
          <el-icon :style="{ color: card.color }" :size="28">
            <component :is="card.icon" />
          </el-icon>
        </div>
      </div>
    </div>

    <!-- 欢迎信息 -->
    <div class="page-card welcome-card">
      <h3>👋 欢迎回来，{{ userStore.adminInfo?.realName || '管理员' }}！</h3>
      <p>今日是 {{ today }}，祝您工作愉快。</p>
    </div>

    <!-- 快速入口 -->
    <div class="page-card">
      <h4 class="card-title">快速入口</h4>
      <div class="quick-links">
        <div
          v-for="link in quickLinks"
          :key="link.path"
          class="quick-link"
          @click="router.push(link.path)"
        >
          <el-icon :size="22" :style="{ color: link.color }"><component :is="link.icon" /></el-icon>
          <span>{{ link.label }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/store/user'
import { formatDateTime } from '@/utils/format'

const router = useRouter()
const userStore = useUserStore()

const today = formatDateTime(new Date(), 'YYYY年MM月DD日')

const statCards = [
  { label: '注册用户', value: '—', desc: '累计用户总数', icon: 'User', color: '#1890ff' },
  { label: '认证陪伴师', value: '—', desc: '已上线陪伴师', icon: 'Notebook', color: '#52c41a' },
  { label: '今日订单', value: '—', desc: '今日新增订单', icon: 'ShoppingCart', color: '#fa8c16' },
  { label: '待审核', value: '—', desc: '内容/资质待审', icon: 'DocumentChecked', color: '#f5222d' }
]

const quickLinks = [
  { label: '用户管理', path: '/user/student', icon: 'User', color: '#1890ff' },
  { label: '陪伴师审核', path: '/tutor/audit', icon: 'DocumentChecked', color: '#52c41a' },
  { label: '社区动态', path: '/community/post', icon: 'ChatDotRound', color: '#fa8c16' },
  { label: '订单管理', path: '/order/list', icon: 'ShoppingCart', color: '#722ed1' },
  { label: '风控管理', path: '/risk/record', icon: 'Warning', color: '#f5222d' },
  { label: '系统配置', path: '/system/config', icon: 'Setting', color: '#13c2c2' }
]
</script>

<style scoped>
.dashboard { padding: 0; }

.stat-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.stat-card {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-left: 4px solid transparent;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.stat-label { font-size: 13px; color: #909399; margin-bottom: 6px; }
.stat-value { font-size: 28px; font-weight: 700; color: #303133; line-height: 1; }
.stat-desc { font-size: 12px; color: #c0c4cc; margin-top: 6px; }

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.welcome-card h3 { font-size: 18px; margin-bottom: 8px; }
.welcome-card p { color: #606266; }

.card-title { font-size: 15px; font-weight: 600; margin-bottom: 16px; color: #303133; }

.quick-links {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.quick-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 20px;
  border: 1px solid #ebeef5;
  border-radius: 10px;
  cursor: pointer;
  min-width: 88px;
  font-size: 13px;
  color: #606266;
  transition: all 0.2s;
}

.quick-link:hover {
  border-color: var(--el-color-primary);
  color: var(--el-color-primary);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(24,144,255,0.15);
}
</style>
