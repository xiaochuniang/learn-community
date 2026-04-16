import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppStore = defineStore('app', () => {
  /** 侧边栏折叠状态 */
  const sidebarCollapsed = ref(false)

  /** 当前设备是否是移动端 */
  const isMobile = ref(window.innerWidth < 768)

  /** 网站标题 */
  const title = ref(import.meta.env.VITE_APP_TITLE || '后台管理系统')

  const sidebarWidth = computed(() => (sidebarCollapsed.value ? '64px' : '220px'))

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  function setSidebarCollapsed(val: boolean) {
    sidebarCollapsed.value = val
  }

  function setIsMobile(val: boolean) {
    isMobile.value = val
  }

  return {
    sidebarCollapsed,
    isMobile,
    title,
    sidebarWidth,
    toggleSidebar,
    setSidebarCollapsed,
    setIsMobile
  }
})
