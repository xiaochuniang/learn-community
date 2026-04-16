import request from '@/utils/request'

/** 帖子列表 */
export const getPostList = (params: object) =>
  request.get('/admin/post/list', params)

/** 帖子详情 */
export const getPostDetail = (id: number) =>
  request.get(`/admin/post/${id}`)

/** 审核帖子 */
export const auditPost = (id: number, data: { status: number; remark: string }) =>
  request.put(`/admin/post/${id}/audit`, data)

/** 删除帖子 */
export const deletePost = (id: number) =>
  request.delete(`/admin/post/${id}`)

/** 话题列表 */
export const getTopicList = (params?: object) =>
  request.get('/admin/topic/list', params)

/** 创建话题 */
export const createTopic = (data: object) =>
  request.post('/admin/topic', data)

/** 更新话题 */
export const updateTopic = (id: number, data: object) =>
  request.put(`/admin/topic/${id}`, data)

/** 删除话题 */
export const deleteTopic = (id: number) =>
  request.delete(`/admin/topic/${id}`)
