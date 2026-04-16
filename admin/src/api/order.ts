import request from '@/utils/request'

/** 订单列表 */
export const getOrderList = (params: object) =>
  request.get('/admin/order/list', params)

/** 订单详情 */
export const getOrderDetail = (id: number) =>
  request.get(`/admin/order/${id}`)

/** 退款 */
export const refundOrder = (id: number, reason: string) =>
  request.post(`/admin/order/${id}/refund`, { reason })

/** 订单统计 */
export const getOrderStats = () =>
  request.get('/admin/order/stats')

/** 佣金流水列表 */
export const getCommissionList = (params: object) =>
  request.get('/admin/commission/list', params)

/** 手动结算佣金 */
export const settleCommission = (id: number) =>
  request.post(`/admin/commission/${id}/settle`)

/** 提现申请列表 */
export const getWithdrawList = (params: object) =>
  request.get('/admin/withdraw/list', params)

/** 审核提现 */
export const auditWithdraw = (id: number, data: { status: number; remark: string }) =>
  request.put(`/admin/withdraw/${id}/audit`, data)
