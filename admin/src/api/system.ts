import request from '@/utils/request'

/** 系统配置列表 */
export const getConfigList = (group?: string) =>
  request.get('/admin/config/list', { group })

/** 保存/更新配置 */
export const saveConfig = (data: { configKey: string; configValue: string }) =>
  request.post('/admin/config/save', data)

/** 批量保存配置 */
export const batchSaveConfig = (list: { configKey: string; configValue: string }[]) =>
  request.post('/admin/config/batch', { list })
