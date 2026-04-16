import request from '@/utils/request'

// ─────────────────────────────────────────────
//  查询参数
// ─────────────────────────────────────────────

export interface TutorListQuery {
  page?: number
  pageSize?: number
  keyword?: string        // 姓名 / 手机号
  auditStatus?: number    // 0待审 1通过 2驳回 3拉黑
  identityType?: number   // 1在校大学生 2应届毕业生 3在职教师 4自由职业
  status?: number
}

// ─────────────────────────────────────────────
//  实体类型
// ─────────────────────────────────────────────

/** 审核列表行 */
export interface TutorAuditItem {
  id: number
  userId: number
  realName: string
  mobile: string
  avatar: string
  identityType: number      // 1在校大学生 2应届毕业生 3在职教师 4自由职业
  education: number         // 1专科 2本科 3硕士 4博士
  school: string
  major: string
  subjects: string          // 逗号分隔
  servicePrice: string      // 元/小时
  auditStatus: number       // 0待审 1通过 2驳回 3拉黑
  auditType: number         // 1入驻申请 2资质变更
  auditRemark: string
  auditTime: string
  auditorName: string
  reviewStatus: number      // 0无需复核 1待复核 2已复核
  blackReason: string
  score: string
  orderCount: number
  totalIncome: string
  createTime: string
}

/** 审核详情（含证件图片） */
export interface TutorAuditDetail extends TutorAuditItem {
  idCardFront: string       // 身份证正面
  idCardBack: string        // 身份证背面
  educationCert: string     // 学历证书
  teacherCert: string       // 教师资格证（选填）
  selfIntro: string         // 自我介绍
  serviceDesc: string       // 服务描述
  tags: string[]            // 擅长标签
  onlineCount: number       // 累计陪伴次数
  historyAudits: AuditLog[] // 历史审核记录
}

/** 历史审核日志 */
export interface AuditLog {
  id: number
  action: string
  remark: string
  operatorName: string
  createTime: string
}

/** 统计数据 */
export interface TutorAuditStats {
  total: number
  pending: number
  approved: number
  rejected: number
  blacklisted: number
}

/** 通用列表响应 */
export interface PageResult<T> {
  list: T[]
  total: number
  page: number
  pageSize: number
}

// ─────────────────────────────────────────────
//  接口
// ─────────────────────────────────────────────

/** 资质审核列表 */
export const getTutorAuditList = (params: TutorListQuery) =>
  request.get<PageResult<TutorAuditItem>>('/admin/tutor/audit/list', params)

/** 审核统计 */
export const getTutorAuditStats = () =>
  request.get<TutorAuditStats>('/admin/tutor/audit/stats')

/** 审核详情 */
export const getTutorAuditDetail = (id: number) =>
  request.get<TutorAuditDetail>(`/admin/tutor/audit/${id}`)

/** 审核通过 */
export const approveTutor = (id: number, remark?: string) =>
  request.put(`/admin/tutor/audit/${id}/approve`, { remark })

/** 审核驳回 */
export const rejectTutor = (id: number, remark: string) =>
  request.put(`/admin/tutor/audit/${id}/reject`, { remark })

/** 拉黑 */
export const blacklistTutor = (id: number, reason: string) =>
  request.put(`/admin/tutor/audit/${id}/blacklist`, { reason })

/** 发起复核 */
export const requestReview = (id: number, remark: string) =>
  request.put(`/admin/tutor/audit/${id}/review`, { remark })

/** 导出审核列表（返回 blob URL） */
export const exportAuditList = (params: TutorListQuery) =>
  request.get('/admin/tutor/audit/export', params)

// ─────────────────────────────────────────────
//  陪伴师管理（列表页使用）
// ─────────────────────────────────────────────

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

export const getTutorList = (params: TutorListQuery) =>
  request.get('/admin/tutor/list', params)

export const getTutorDetail = (id: number) =>
  request.get<TutorItem>(`/admin/tutor/${id}`)

export const auditTutor = (id: number, data: { status: number; remark: string }) =>
  request.put(`/admin/tutor/${id}/audit`, data)

export const updateTutorStatus = (id: number, status: number) =>
  request.put(`/admin/tutor/${id}/status`, { status })
