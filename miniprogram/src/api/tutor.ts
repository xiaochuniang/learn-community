import request from '@/utils/request'

// ── 身份类型 ─────────────────────────────────────────────────────
export type TutorIdentityType = 'college_student' | 'teacher' | 'psychologist' | 'other'

// ── 申请状态 ─────────────────────────────────────────────────────
export type TutorApplyStatus = 'draft' | 'pending' | 'approved' | 'rejected' | 'blacklisted'

// ── 申请提交参数 ─────────────────────────────────────────────────
export interface TutorApplyParams {
  identityType: TutorIdentityType
  // 基础信息
  realName: string
  idCardNo: string
  gender: number              // 1 男 2 女
  birthday: string            // YYYY-MM-DD
  mobile: string
  city: string
  school: string
  education: string           // 学历：高中/大专/本科/硕士/博士
  major: string
  // 服务信息
  subjects: string[]          // 擅长科目
  grades: string[]            // 擅长年级
  servicePrice: number        // 单位：元/小时
  introduction: string        // 个人简介（不少于50字）
  // 文件 key（通过 uploadFile 上传后获得）
  idCardFrontKey: string
  idCardBackKey: string
  qualificationKeys: string[] // 资质证明（1~5张）
  criminalRecordKey: string   // 无犯罪记录证明
  faceVerified: boolean       // 人脸核验是否通过
  agreeTerms: boolean         // 承诺书
}

// ── 申请结果 ──────────────────────────────────────────────────────
export interface TutorApplyResult {
  applyId: number
  status: TutorApplyStatus
  submitTime: string
}

// ── 申请详情（用于回显）────────────────────────────────────────────
export interface TutorApplyDetail extends TutorApplyParams {
  applyId: number
  status: TutorApplyStatus
  rejectReason: string
  submitTime: string
  updateTime: string
}

// ── 文件上传结果 ─────────────────────────────────────────────────
export interface UploadFileResult {
  fileKey: string
  fileUrl: string
}

// ═══════════════════════════════════════════════════════════════════
// 接口方法
// ═══════════════════════════════════════════════════════════════════

/** 提交入驻申请 */
export const submitTutorApply = (data: TutorApplyParams) =>
  request.post<TutorApplyResult>('/tutor/apply', data as unknown as Record<string, unknown>)

/** 查询本人申请状态（已有申请时回显） */
export const getMyApply = () =>
  request.get<TutorApplyDetail>('/tutor/apply/my')

/** 上传入驻相关文件 */
export const uploadTutorFile = (filePath: string, type: string): Promise<import('@/utils/request').ApiResult<UploadFileResult>> =>
  request.upload<UploadFileResult>('/tutor/apply/upload', filePath, { type })

/** 发起人脸核验（获取核验 token） */
export const startFaceVerify = () =>
  request.post<{ verifyToken: string; certifyUrl: string }>('/tutor/face-verify/start')

/** 查询人脸核验结果 */
export const checkFaceVerify = (verifyToken: string) =>
  request.get<{ verified: boolean }>('/tutor/face-verify/check', { verifyToken })
