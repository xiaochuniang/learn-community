import request from '@/utils/request'

/** 风控记录列表 */
export const getRiskList = (params: object) =>
  request.get('/admin/risk/list', params)

/** 处理风控记录 */
export const handleRisk = (id: number, data: { handleType: number; handleRemark: string }) =>
  request.put(`/admin/risk/${id}/handle`, data)

/** 用户封禁列表 */
export const getBanList = (params: object) =>
  request.get('/admin/risk/ban/list', params)

/** 封禁用户 */
export const banUser = (data: {
  userId: number
  banType: number
  reason: string
  endTime?: string
}) => request.post('/admin/risk/ban', data)

/** 解封用户 */
export const unbanUser = (id: number) =>
  request.put(`/admin/risk/ban/${id}/unban`)
