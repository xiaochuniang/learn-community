/**
 * 本地存储工具
 */
const TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY || 'lc_admin_token'

export const storage = {
  /** 获取 token */
  getToken(): string {
    return localStorage.getItem(TOKEN_KEY) || ''
  },

  /** 保存 token */
  setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token)
  },

  /** 删除 token */
  removeToken(): void {
    localStorage.removeItem(TOKEN_KEY)
  },

  /** 通用 get */
  get<T>(key: string): T | null {
    const raw = localStorage.getItem(key)
    if (!raw) return null
    try {
      return JSON.parse(raw) as T
    } catch {
      return raw as unknown as T
    }
  },

  /** 通用 set */
  set(key: string, value: unknown): void {
    localStorage.setItem(key, JSON.stringify(value))
  },

  /** 通用 remove */
  remove(key: string): void {
    localStorage.removeItem(key)
  },

  /** 清空所有 */
  clear(): void {
    localStorage.clear()
  }
}
