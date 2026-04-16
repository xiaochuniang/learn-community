import request from '@/utils/request'

export interface LoginParams {
  username: string
  password: string
}

export interface LoginResult {
  token: string
  adminInfo: {
    id: number
    username: string
    realName: string
    avatar: string
    roleName: string
    permissions: string[]
  }
}

export interface AdminInfo {
  id: number
  username: string
  realName: string
  avatar: string
  email: string
  mobile: string
  roleId: number
  roleName: string
  status: number
  lastLoginAt: string
}

/** 登录 */
export const login = (data: LoginParams) =>
  request.post<LoginResult>('/admin/auth/login', data)

/** 获取当前管理员信息 */
export const getAdminInfo = () =>
  request.get<AdminInfo>('/admin/auth/info')

/** 退出登录 */
export const logout = () =>
  request.post('/admin/auth/logout')

/** 修改密码 */
export const changePassword = (data: { oldPassword: string; newPassword: string }) =>
  request.put('/admin/auth/password', data)
