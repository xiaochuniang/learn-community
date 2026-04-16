import request from '@/utils/request'

/** 微信登录（code 换 token） */
export const wxLogin = (code: string) =>
  request.post<{ token: string; isNewUser: boolean }>('/auth/wx/login', { code })

/** 绑定手机号（加密方式） */
export const bindMobile = (data: {
  encryptedData: string
  iv: string
  code: string
}) => request.post<{ token: string }>('/auth/wx/bind-mobile', data)

/** 刷新 token */
export const refreshToken = () =>
  request.post<{ token: string }>('/auth/refresh')

/** 退出登录 */
export const logout = () =>
  request.post('/auth/logout')
