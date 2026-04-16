<template>
  <header class="app-header">
    <!-- 折叠按钮 -->
    <div class="header-left">
      <el-icon class="collapse-btn" @click="appStore.toggleSidebar">
        <Fold v-if="!appStore.sidebarCollapsed" />
        <Expand v-else />
      </el-icon>

      <!-- 面包屑 -->
      <el-breadcrumb separator="/" class="breadcrumb">
        <el-breadcrumb-item :to="{ path: '/dashboard' }">首页</el-breadcrumb-item>
        <el-breadcrumb-item v-for="(item, idx) in breadcrumbs" :key="idx">
          {{ item.title }}
        </el-breadcrumb-item>
      </el-breadcrumb>
    </div>

    <!-- 右侧操作区 -->
    <div class="header-right">
      <!-- 全屏 -->
      <el-tooltip content="全屏" placement="bottom">
        <el-icon class="action-icon" @click="toggleFullscreen">
          <FullScreen />
        </el-icon>
      </el-tooltip>

      <!-- 用户信息下拉 -->
      <el-dropdown trigger="click" @command="handleCommand">
        <div class="user-info">
          <el-avatar :size="32" :src="userStore.adminInfo?.avatar || ''" icon="UserFilled" />
          <span class="username">{{ userStore.adminInfo?.realName || userStore.adminInfo?.username }}</span>
          <el-icon><ArrowDown /></el-icon>
        </div>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="profile">
              <el-icon><User /></el-icon> 个人信息
            </el-dropdown-item>
            <el-dropdown-item command="password">
              <el-icon><Lock /></el-icon> 修改密码
            </el-dropdown-item>
            <el-dropdown-item divided command="logout">
              <el-icon><SwitchButton /></el-icon> 退出登录
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox } from 'element-plus'
import { useAppStore } from '@/store/app'
import { useUserStore } from '@/store/user'

const appStore = useAppStore()
const userStore = useUserStore()
const route = useRoute()
const router = useRouter()

const breadcrumbs = computed(() => {
  return route.matched
    .filter((r) => r.meta?.title && !r.meta?.hidden && r.path !== '/')
    .map((r) => ({ title: r.meta?.title as string }))
})

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}

async function handleCommand(cmd: string) {
  if (cmd === 'logout') {
    await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await userStore.doLogout()
    router.push('/login')
  }
}
</script>

<style scoped>
.app-header {
  height: 56px;
  background: #fff;
  border-bottom: 1px solid #ebeef5;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  flex-shrink: 0;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.collapse-btn {
  font-size: 20px;
  cursor: pointer;
  color: #606266;
  transition: color 0.2s;
}

.collapse-btn:hover {
  color: var(--el-color-primary);
}

.breadcrumb {
  font-size: 13px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.action-icon {
  font-size: 18px;
  cursor: pointer;
  color: #606266;
  transition: color 0.2s;
}

.action-icon:hover {
  color: var(--el-color-primary);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background 0.2s;
}

.user-info:hover {
  background: #f5f7fa;
}

.username {
  font-size: 13px;
  color: #303133;
  max-width: 80px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
