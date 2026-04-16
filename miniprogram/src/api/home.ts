import request from '@/utils/request'

export interface BannerItem {
  id: number
  imageUrl: string
  linkType: string  // 'course' | 'tutor' | 'external'
  linkId: number
  linkUrl: string
}

export interface CourseItem {
  id: number
  title: string
  coverUrl: string
  categoryName: string
  teacherName: string
  price: string
  originalPrice: string
  studentCount: number
  rating: string
  isFree: boolean
}

export interface TutorItem {
  id: number
  realName: string
  avatar: string
  subjects: string
  school: string
  education: string
  servicePrice: string
  score: string
  orderCount: number
  tags: string[]
}

export interface NoticeItem {
  id: number
  title: string
  content: string
  type: number
  createTime: string
}

/** 首页 banner */
export const getHomeBanners = () =>
  request.get<BannerItem[]>('/home/banners')

/** 推荐课程 */
export const getRecommendCourses = (limit = 6) =>
  request.get<CourseItem[]>('/home/recommend/courses', { limit })

/** 推荐陪伴师 */
export const getRecommendTutors = (limit = 4) =>
  request.get<TutorItem[]>('/home/recommend/tutors', { limit })

/** 系统公告 */
export const getNotices = () =>
  request.get<NoticeItem[]>('/home/notices')

/** 课程列表 */
export const getCourseList = (params: {
  page: number
  pageSize: number
  keyword?: string
  categoryId?: number
  isFree?: boolean
  sort?: string
}) => request.get<{ list: CourseItem[]; total: number }>('/course/list', params as Record<string, unknown>)

/** 课程详情 */
export const getCourseDetail = (id: number) =>
  request.get<CourseItem>(`/course/${id}`)
