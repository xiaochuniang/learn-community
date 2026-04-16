import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { storage } from '@/utils/storage'
import { login, logout, getAdminInfo, type LoginParams, type AdminInfo } from '@/api/auth'
import { ElMessage } from 'element-plus'

export const useUserStore = defineStore('user', () => {
  const token = ref<string>(storage.getToken())
  const adminInfo = ref<AdminInfo | null>(null)

  const isLoggedIn = computed(() => !!token.value)
  const permissions = ref<string[]>([])

  /** 登录 */
  async function doLogin(params: LoginParams) {
    const res = await login(params)
    token.value = res.data.token
    storage.setToken(res.data.token)
    adminInfo.value = res.data.adminInfo as unknown as AdminInfo
    permissions.value = res.data.adminInfo.permissions
    storage.set('adminInfo', adminInfo.value)
    return res
  }

  /** 获取用户信息 */
  async function fetchAdminInfo() {
    if (adminInfo.value) return adminInfo.value
    const cached = storage.get<AdminInfo>('adminInfo')
    if (cached) {
      adminInfo.value = cached
      return cached
    }
    const res = await getAdminInfo()
    adminInfo.value = res.data
    storage.set('adminInfo', res.data)
    return res.data
  }

  /** 退出登录 */
  async function doLogout() {
    try {
      await logout()
    } catch {
      // 忽略服务端错误，强制前端登出
    }
    token.value = ''
    adminInfo.value = null
    permissions.value = []
    storage.removeToken()
    storage.remove('adminInfo')
    ElMessage.success('已安全退出')
  }

  /** 校验是否有某权限 */
  function hasPermission(perm: string): boolean {
    if (permissions.value.includes('*')) return true
    return permissions.value.includes(perm)
  }

  return {
    token,
    adminInfo,
    isLoggedIn,
    permissions,
    doLogin,
    fetchAdminInfo,
    doLogout,
    hasPermission
  }
})
