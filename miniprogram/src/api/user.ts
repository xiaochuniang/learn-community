import request from '@/utils/request'

export interface UserInfo {
  id: number
  openid: string
  nickName: string
  avatarUrl: string
  gender: number        // 0未知 1男 2女
  mobile: string
  grade: string         // 年级
  school: string
  points: number        // 积分
  vipExpireTime: string | null
  isVip: boolean
  createTime: string
}

export interface UpdateProfileParams {
  nickName?: string
  avatarUrl?: string
  gender?: number
  grade?: string
  school?: string
}

/** 获取当前用户信息 */
export const getUserInfo = () =>
  request.get<UserInfo>('/user/info')

/** 更新用户资料 */
export const updateProfile = (data: UpdateProfileParams) =>
  request.put<UserInfo>('/user/profile', data as Record<string, unknown>)

/** 获取用户积分明细 */
export const getPointsHistory = (params: { page: number; pageSize: number }) =>
  request.get<{ list: PointRecord[]; total: number }>('/user/points/history', params as Record<string, unknown>)

export interface PointRecord {
  id: number
  type: string
  points: number
  remark: string
  createTime: string
}
