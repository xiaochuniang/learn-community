import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export interface TabItem {
  path: string
  title: string
  name: string
}

export const useTabsStore = defineStore('tabs', () => {
  const router = useRouter()

  const tabs = ref<TabItem[]>([
    { path: '/dashboard', title: '工作台', name: 'Dashboard' }
  ])
  const activeTab = ref('/dashboard')

  function addTab(tab: TabItem) {
    const exists = tabs.value.find((t) => t.path === tab.path)
    if (!exists) {
      tabs.value.push(tab)
    }
    activeTab.value = tab.path
  }

  function removeTab(path: string) {
    const idx = tabs.value.findIndex((t) => t.path === path)
    if (idx === -1) return
    tabs.value.splice(idx, 1)
    // 关闭的是当前激活的标签则跳转到前一个
    if (activeTab.value === path) {
      const next = tabs.value[idx - 1] || tabs.value[0]
      if (next) {
        activeTab.value = next.path
        router.push(next.path)
      }
    }
  }

  function closeOthers(path: string) {
    tabs.value = tabs.value.filter((t) => t.path === '/dashboard' || t.path === path)
    activeTab.value = path
  }

  function closeAll() {
    tabs.value = [{ path: '/dashboard', title: '工作台', name: 'Dashboard' }]
    activeTab.value = '/dashboard'
    router.push('/dashboard')
  }

  function setActiveTab(path: string) {
    activeTab.value = path
  }

  return {
    tabs,
    activeTab,
    addTab,
    removeTab,
    closeOthers,
    closeAll,
    setActiveTab
  }
})
