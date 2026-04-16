import request from '@/utils/request'

export interface TutorListQuery {
  page?: number
  pageSize?: number
  keyword?: string
  auditStatus?: number
  status?: number
}

export interface TutorItem {
  id: number
  userId: number
  realName: string
  mobile: string
  avatar: string
  education: number
  school: string
  subjects: string
  servicePrice: string
  auditStatus: number
  status: number
  score: string
  orderCount: number
  totalIncome: string
  createTime: string
}

/** 陪伴师列表 */
export const getTutorList = (params: TutorListQuery) =>
  request.get('/admin/tutor/list', params)

/** 陪伴师详情 */
export const getTutorDetail = (id: number) =>
  request.get(`/admin/tutor/${id}`)

/** 审核陪伴师 */
export const auditTutor = (id: number, data: { status: number; remark: string }) =>
  request.put(`/admin/tutor/${id}/audit`, data)

/** 上/下线陪伴师 */
export const updateTutorStatus = (id: number, status: number) =>
  request.put(`/admin/tutor/${id}/status`, { status })

/** 资质审核列表 */
export const getTutorAuditList = (params: TutorListQuery) =>
  request.get('/admin/tutor/audit/list', params)
