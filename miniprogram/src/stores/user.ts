import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { storage } from '@/utils/storage'
import { wxLogin } from '@/api/auth'
import { getUserInfo, type UserInfo } from '@/api/user'

export const useUserStore = defineStore('user', () => {
  // ── 状态 ─────────────────────────────────────────────────────────
  const token = ref<string>('')
  const userInfo = ref<UserInfo | null>(null)
  const loading = ref(false)

  // ── 计算属性 ──────────────────────────────────────────────────────
  const isLoggedIn = computed(() => !!token.value)
  const isVip = computed(() => userInfo.value?.isVip ?? false)
  const nickName = computed(() => userInfo.value?.nickName || '未登录')
  const avatarUrl = computed(
    () => userInfo.value?.avatarUrl || '/static/images/default-avatar.png'
  )

  // ── 初始化（App.vue 启动时调用） ──────────────────────────────────
  async function initFromStorage() {
    const savedToken = storage.getToken()
    if (!savedToken) return
    token.value = savedToken
    try {
      await fetchUserInfo()
    } catch {
      // token 失效，清除
      clearUser()
    }
  }

  // ── 微信一键登录 ──────────────────────────────────────────────────
  async function login(): Promise<boolean> {
    loading.value = true
    try {
      const { code } = await uni.login({ provider: 'weixin' })
      const res = await wxLogin(code)
      token.value = res.data.token
      storage.setToken(res.data.token)
      await fetchUserInfo()
      return true
    } catch (e) {
      console.error('[UserStore] login failed:', e)
      return false
    } finally {
      loading.value = false
    }
  }

  // ── 拉取用户信息 ─────────────────────────────────────────────────
  async function fetchUserInfo() {
    const res = await getUserInfo()
    userInfo.value = res.data
  }

  // ── 更新本地 userInfo 某字段 ─────────────────────────────────────
  function patchUserInfo(patch: Partial<UserInfo>) {
    if (userInfo.value) {
      userInfo.value = { ...userInfo.value, ...patch }
    }
  }

  // ── 退出登录 ─────────────────────────────────────────────────────
  function clearUser() {
    token.value = ''
    userInfo.value = null
    storage.removeToken()
  }

  async function logout() {
    try {
      // 可选：调用后端登出接口
      const { logout: apiLogout } = await import('@/api/auth')
      await apiLogout()
    } catch { /* ignore */ }
    clearUser()
    uni.reLaunch({ url: '/pages/login/index' })
  }

  return {
    token,
    userInfo,
    loading,
    isLoggedIn,
    isVip,
    nickName,
    avatarUrl,
    initFromStorage,
    login,
    fetchUserInfo,
    patchUserInfo,
    clearUser,
    logout
  }
})
