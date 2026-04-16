import axios, { type AxiosInstance, type AxiosRequestConfig, type AxiosResponse } from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'
import { storage } from '@/utils/storage'
import router from '@/router'

/** 统一响应结构 */
export interface ApiResult<T = unknown> {
  code: number
  message: string
  data: T
}

const service: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 15000,
  headers: { 'Content-Type': 'application/json;charset=utf-8' }
})

// -------- 请求拦截器 --------
service.interceptors.request.use(
  (config) => {
    const token = storage.getToken()
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// -------- 响应拦截器 --------
service.interceptors.response.use(
  (response: AxiosResponse<ApiResult>) => {
    const res = response.data
    // 业务成功码：0 或 200
    if (res.code === 0 || res.code === 200) {
      return res as unknown as AxiosResponse
    }
    // 登录失效
    if (res.code === 401) {
      ElMessageBox.confirm('登录状态已过期，请重新登录', '系统提示', {
        confirmButtonText: '重新登录',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        storage.removeToken()
        router.push('/login')
      })
      return Promise.reject(new Error(res.message))
    }
    ElMessage.error(res.message || '请求失败')
    return Promise.reject(new Error(res.message))
  },
  (error) => {
    const status = error.response?.status
    const msgMap: Record<number, string> = {
      400: '请求参数错误',
      401: '未授权，请重新登录',
      403: '拒绝访问',
      404: '请求地址不存在',
      500: '服务器内部错误',
      502: '网关错误',
      503: '服务不可用'
    }
    ElMessage.error(msgMap[status] || error.message || '网络异常')
    return Promise.reject(error)
  }
)

/** 泛型请求方法 */
const request = {
  get<T = unknown>(url: string, params?: object, config?: AxiosRequestConfig): Promise<ApiResult<T>> {
    return service.get(url, { params, ...config }) as unknown as Promise<ApiResult<T>>
  },
  post<T = unknown>(url: string, data?: object, config?: AxiosRequestConfig): Promise<ApiResult<T>> {
    return service.post(url, data, config) as unknown as Promise<ApiResult<T>>
  },
  put<T = unknown>(url: string, data?: object, config?: AxiosRequestConfig): Promise<ApiResult<T>> {
    return service.put(url, data, config) as unknown as Promise<ApiResult<T>>
  },
  delete<T = unknown>(url: string, params?: object, config?: AxiosRequestConfig): Promise<ApiResult<T>> {
    return service.delete(url, { params, ...config }) as unknown as Promise<ApiResult<T>>
  },
  upload<T = unknown>(url: string, formData: FormData): Promise<ApiResult<T>> {
    return service.post(url, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }) as unknown as Promise<ApiResult<T>>
  }
}

export default request
