<template>
  <div class="tags-view">
    <el-scrollbar class="tags-scroll">
      <div class="tags-inner">
        <div
          v-for="tab in tabsStore.tabs"
          :key="tab.path"
          class="tag-item"
          :class="{ active: tabsStore.activeTab === tab.path }"
          @click="handleClick(tab)"
          @contextmenu.prevent="openContextMenu($event, tab)"
        >
          <span>{{ tab.title }}</span>
          <el-icon
            v-if="tab.path !== '/dashboard'"
            class="tag-close"
            @click.stop="tabsStore.removeTab(tab.path)"
          >
            <Close />
          </el-icon>
        </div>
      </div>
    </el-scrollbar>

    <!-- 右键菜单 -->
    <ul v-if="contextMenu.visible" class="context-menu" :style="contextMenu.style">
      <li @click="closeOthers">关闭其他</li>
      <li @click="tabsStore.closeAll">关闭全部</li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTabsStore, type TabItem } from '@/store/tabs'

const tabsStore = useTabsStore()
const router = useRouter()

const contextMenu = reactive({
  visible: false,
  style: {} as Record<string, string>,
  tab: null as TabItem | null
})

function handleClick(tab: TabItem) {
  tabsStore.setActiveTab(tab.path)
  router.push(tab.path)
}

function openContextMenu(e: MouseEvent, tab: TabItem) {
  contextMenu.visible = true
  contextMenu.tab = tab
  contextMenu.style = {
    left: e.clientX + 'px',
    top: e.clientY + 'px'
  }
}

function closeOthers() {
  if (contextMenu.tab) {
    tabsStore.closeOthers(contextMenu.tab.path)
    router.push(contextMenu.tab.path)
  }
  contextMenu.visible = false
}

function hideContextMenu() {
  contextMenu.visible = false
}

onMounted(() => document.addEventListener('click', hideContextMenu))
onUnmounted(() => document.removeEventListener('click', hideContextMenu))
</script>

<style scoped>
.tags-view {
  height: 40px;
  background: #fff;
  border-bottom: 1px solid #ebeef5;
  padding: 0 8px;
  position: relative;
  flex-shrink: 0;
}

.tags-scroll {
  height: 40px;
}

.tags-inner {
  display: flex;
  align-items: center;
  height: 40px;
  gap: 4px;
  padding: 4px 0;
}

.tag-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0 10px;
  height: 28px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  font-size: 12px;
  color: #606266;
  cursor: pointer;
  white-space: nowrap;
  background: #fff;
  transition: all 0.15s;
  user-select: none;
}

.tag-item:hover {
  color: var(--el-color-primary);
  border-color: var(--el-color-primary);
}

.tag-item.active {
  background: var(--el-color-primary);
  color: #fff;
  border-color: var(--el-color-primary);
}

.tag-close {
  font-size: 12px;
  border-radius: 50%;
  padding: 1px;
  transition: background 0.15s;
}

.tag-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.context-menu {
  position: fixed;
  z-index: 9999;
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 4px 0;
  list-style: none;
  min-width: 120px;
}

.context-menu li {
  padding: 8px 16px;
  font-size: 13px;
  cursor: pointer;
  color: #303133;
  transition: background 0.15s;
}

.context-menu li:hover {
  background: #f5f7fa;
  color: var(--el-color-primary);
}
</style>
