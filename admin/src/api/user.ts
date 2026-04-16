import request from '@/utils/request'

export interface UserListQuery {
  page?: number
  pageSize?: number
  keyword?: string
  userType?: number
  status?: number
  startDate?: string
  endDate?: string
}

export interface UserItem {
  id: number
  nickname: string
  realName: string
  mobile: string
  avatar: string
  userType: number
  status: number
  totalStudyMin: number
  balance: string
  lastLoginAt: string
  createTime: string
}

export interface PageResult<T> {
  list: T[]
  total: number
  page: number
  pageSize: number
}

/** 用户列表 */
export const getUserList = (params: UserListQuery) =>
  request.get<PageResult<UserItem>>('/admin/user/list', params)

/** 用户详情 */
export const getUserDetail = (id: number) =>
  request.get<UserItem>(`/admin/user/${id}`)

/** 禁用/启用用户 */
export const updateUserStatus = (id: number, status: number) =>
  request.put(`/admin/user/${id}/status`, { status })

/** 删除用户 */
export const deleteUser = (id: number) =>
  request.delete(`/admin/user/${id}`)

/** 用户统计 */
export const getUserStats = () =>
  request.get('/admin/user/stats')
