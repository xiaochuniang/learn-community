// ─── Token ────────────────────────────────────────────────────────
const TOKEN_KEY = 'lc_token'

export const storage = {
  getToken(): string {
    return uni.getStorageSync(TOKEN_KEY) || ''
  },
  setToken(token: string): void {
    uni.setStorageSync(TOKEN_KEY, token)
  },
  removeToken(): void {
    uni.removeStorageSync(TOKEN_KEY)
  },

  get<T>(key: string): T | null {
    try {
      const raw = uni.getStorageSync(key)
      if (raw === '' || raw === null || raw === undefined) return null
      if (typeof raw === 'string') {
        try { return JSON.parse(raw) as T } catch { return raw as unknown as T }
      }
      return raw as T
    } catch {
      return null
    }
  },

  set(key: string, value: unknown): void {
    uni.setStorageSync(key, typeof value === 'string' ? value : JSON.stringify(value))
  },

  remove(key: string): void {
    uni.removeStorageSync(key)
  },

  clear(): void {
    uni.clearStorageSync()
  }
}
