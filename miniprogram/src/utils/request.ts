// ─── 环境配置 ────────────────────────────────────────────────────
const BASE_URL = import.meta.env.VITE_API_BASE_URL as string || 'https://api.learncommunity.com'
const TIMEOUT = 15000

// ─── 统一响应结构 ─────────────────────────────────────────────────
export interface ApiResult<T = unknown> {
  code: number
  message: string
  data: T
}

// ─── Token 来源（延迟导入避免循环依赖） ────────────────────────────
function getToken(): string {
  return uni.getStorageSync('lc_token') || ''
}

// ─── 请求选项 ─────────────────────────────────────────────────────
interface RequestOptions {
  url: string
  method: 'GET' | 'POST' | 'PUT' | 'DELETE'
  data?: Record<string, unknown>
  header?: Record<string, string>
}

// ─── 核心封装 ─────────────────────────────────────────────────────
function http<T = unknown>(options: RequestOptions): Promise<ApiResult<T>> {
  return new Promise((resolve, reject) => {
    const token = getToken()
    const header: Record<string, string> = {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options.header || {})
    }

    // 显示 loading（POST/PUT/DELETE 才显示，GET 通常页面内自管）
    if (options.method !== 'GET') {
      uni.showLoading({ title: '请稍候...', mask: true })
    }

    uni.request({
      url: `${BASE_URL}${options.url}`,
      method: options.method,
      data: options.data,
      header,
      timeout: TIMEOUT,
      success: (res) => {
        const result = res.data as ApiResult<T>

        // 业务成功
        if (result.code === 0 || result.code === 200) {
          resolve(result)
          return
        }

        // 登录失效
        if (result.code === 401) {
          uni.removeStorageSync('lc_token')
          uni.navigateTo({ url: '/pages/login/index' })
          reject(new Error('登录已过期，请重新登录'))
          return
        }

        uni.showToast({ title: result.message || '请求失败', icon: 'none' })
        reject(new Error(result.message))
      },
      fail: (err) => {
        uni.showToast({ title: '网络异常，请稍后重试', icon: 'none' })
        reject(err)
      },
      complete: () => {
        if (options.method !== 'GET') {
          uni.hideLoading()
        }
      }
    })
  })
}

// ─── 语义化方法 ───────────────────────────────────────────────────
const request = {
  get<T = unknown>(url: string, params?: Record<string, unknown>): Promise<ApiResult<T>> {
    // uni.request GET 参数拼接到 url
    if (params && Object.keys(params).length > 0) {
      const qs = Object.entries(params)
        .filter(([, v]) => v !== undefined && v !== null && v !== '')
        .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`)
        .join('&')
      url = qs ? `${url}?${qs}` : url
    }
    return http<T>({ url, method: 'GET' })
  },

  post<T = unknown>(url: string, data?: Record<string, unknown>): Promise<ApiResult<T>> {
    return http<T>({ url, method: 'POST', data })
  },

  put<T = unknown>(url: string, data?: Record<string, unknown>): Promise<ApiResult<T>> {
    return http<T>({ url, method: 'PUT', data })
  },

  delete<T = unknown>(url: string, data?: Record<string, unknown>): Promise<ApiResult<T>> {
    return http<T>({ url, method: 'DELETE', data })
  },

  /** 上传文件（单文件） */
  upload<T = unknown>(url: string, filePath: string, formData?: Record<string, unknown>): Promise<ApiResult<T>> {
    return new Promise((resolve, reject) => {
      uni.uploadFile({
        url: `${BASE_URL}${url}`,
        filePath,
        name: 'file',
        header: {
          Authorization: `Bearer ${getToken()}`
        },
        formData,
        success: (res) => {
          const result: ApiResult<T> = JSON.parse(res.data)
          if (result.code === 0 || result.code === 200) {
            resolve(result)
          } else {
            uni.showToast({ title: result.message || '上传失败', icon: 'none' })
            reject(new Error(result.message))
          }
        },
        fail: (err) => {
          uni.showToast({ title: '上传失败', icon: 'none' })
          reject(err)
        }
      })
    })
  }
}

export default request
