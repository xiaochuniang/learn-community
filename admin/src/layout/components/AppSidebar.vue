<template>
  <aside class="app-sidebar" :class="{ collapsed: appStore.sidebarCollapsed }">
    <!-- Logo -->
    <div class="sidebar-logo">
      <span class="logo-icon">📚</span>
      <span v-if="!appStore.sidebarCollapsed" class="logo-text">学习社区后台</span>
    </div>

    <!-- 菜单 -->
    <el-scrollbar class="sidebar-scroll">
      <el-menu
        :default-active="activeMenu"
        :collapse="appStore.sidebarCollapsed"
        :collapse-transition="false"
        background-color="#001529"
        text-color="rgba(255,255,255,0.65)"
        active-text-color="#fff"
        router
        unique-opened
      >
        <template v-for="item in menuRoutes" :key="item.path">
          <!-- 多子菜单 -->
          <el-sub-menu v-if="visibleChildren(item).length > 1" :index="item.path">
            <template #title>
              <el-icon><component :is="item.meta?.icon || 'Menu'" /></el-icon>
              <span>{{ item.meta?.title }}</span>
            </template>
            <el-menu-item
              v-for="child in visibleChildren(item)"
              :key="child.path"
              :index="`${item.path}/${child.path}`"
            >
              <el-icon><component :is="child.meta?.icon || 'Document'" /></el-icon>
              <template #title>{{ child.meta?.title }}</template>
            </el-menu-item>
          </el-sub-menu>

          <!-- 单子菜单：直接显示父级 title，跳转子路由 -->
          <el-menu-item
            v-else-if="visibleChildren(item).length === 1"
            :index="`${item.path}/${visibleChildren(item)[0].path}`"
          >
            <el-icon><component :is="item.meta?.icon || 'Document'" /></el-icon>
            <template #title>{{ item.meta?.title || visibleChildren(item)[0].meta?.title }}</template>
          </el-menu-item>

          <!-- 无子菜单 -->
          <el-menu-item v-else :index="item.path">
            <el-icon><component :is="item.meta?.icon || 'Document'" /></el-icon>
            <template #title>{{ item.meta?.title }}</template>
          </el-menu-item>
        </template>
      </el-menu>
    </el-scrollbar>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/store/app'
import { routes } from '@/router'
import type { RouteRecordRaw } from 'vue-router'

const appStore = useAppStore()
const route = useRoute()

const activeMenu = computed(() => route.path)

const menuRoutes = computed(() =>
  routes.filter(
    (r) => !r.meta?.hidden && r.path !== '/' && r.path !== '/:pathMatch(.*)*'
  )
)

function visibleChildren(item: RouteRecordRaw): RouteRecordRaw[] {
  if (!item.children) return []
  return item.children.filter((c) => !c.meta?.hidden)
}
</script>

<style scoped>
.app-sidebar {
  width: 220px;
  height: 100vh;
  background: #001529;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  transition: width 0.3s ease;
  overflow: hidden;
}

.app-sidebar.collapsed {
  width: 64px;
}

.sidebar-logo {
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
  white-space: nowrap;
  flex-shrink: 0;
}

.logo-icon {
  font-size: 22px;
  flex-shrink: 0;
}

.logo-text {
  font-size: 15px;
  font-weight: 600;
  color: #fff;
  letter-spacing: 0.5px;
}

.sidebar-scroll {
  flex: 1;
  height: calc(100vh - 56px);
}

:deep(.el-menu) {
  border-right: none;
}

:deep(.el-menu--collapse) {
  width: 64px;
}

:deep(.el-menu-item.is-active) {
  background-color: var(--el-color-primary) !important;
}

:deep(.el-sub-menu__title:hover),
:deep(.el-menu-item:hover) {
  background-color: rgba(255, 255, 255, 0.08) !important;
  color: #fff !important;
}
</style>
