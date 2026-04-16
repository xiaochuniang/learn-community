<template>
  <div class="app-layout" :class="{ 'sidebar-collapsed': appStore.sidebarCollapsed }">
    <!-- 侧边栏 -->
    <AppSidebar />

    <!-- 右侧内容区 -->
    <div class="main-container" :style="{ marginLeft: appStore.sidebarWidth }">
      <!-- 顶部导航 -->
      <AppHeader />

      <!-- 标签栏 -->
      <TagsView />

      <!-- 页面内容 -->
      <div class="page-content">
        <router-view v-slot="{ Component, route }">
          <transition name="fade" mode="out-in">
            <keep-alive>
              <component :is="Component" :key="route.path" />
            </keep-alive>
          </transition>
        </router-view>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useAppStore } from '@/store/app'
import AppSidebar from './components/AppSidebar.vue'
import AppHeader from './components/AppHeader.vue'
import TagsView from './components/TagsView.vue'

const appStore = useAppStore()

function handleResize() {
  const mobile = window.innerWidth < 768
  appStore.setIsMobile(mobile)
  appStore.setSidebarCollapsed(mobile)
}

onMounted(() => window.addEventListener('resize', handleResize))
onUnmounted(() => window.removeEventListener('resize', handleResize))
</script>

<style scoped>
.app-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
}

.main-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: margin-left 0.3s ease;
}

.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f0f2f5;
}
</style>
