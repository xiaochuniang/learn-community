import request from '@/utils/request'

// ── 类型 ─────────────────────────────────────────────────────────────

export interface MapPointItem {
  id: number
  key: string
  name: string
  icon: string
  description: string
  grid_x: number
  grid_y: number
  build_height: number
  color_top: string
  color_left: string
  color_right: string
  target_path: string
  is_home: number
  sort: number
  status: number
  create_time: string
  update_time: string
}

export interface CharacterConfigItem {
  id: number
  name: string
  body_color: string
  skin_color: string
  hair_color: string
  walk_speed: number
  home_grid_x: number
  home_grid_y: number
}

// ── 点位管理 ─────────────────────────────────────────────────────────

/** 点位列表（分页） */
export const getAdminMapPoints = (params: { page: number; limit: number }) =>
  request.get<{ list: MapPointItem[]; total: number; page: number; limit: number }>(
    '/map/adminPoints',
    params as Record<string, unknown>,
  )

/** 新增/编辑点位 */
export const saveMapPoint = (data: Partial<MapPointItem>) =>
  request.post<{ id: number }>('/map/savePoint', data as Record<string, unknown>)

/** 删除点位 */
export const deleteMapPoint = (id: number) =>
  request.post('/map/deletePoint', { id } as Record<string, unknown>)

// ── 人物配置 ─────────────────────────────────────────────────────────

/** 查询人物配置 */
export const getAdminCharacter = () =>
  request.get<CharacterConfigItem>('/map/characterConfig')

/** 保存人物配置 */
export const saveCharacterConfig = (data: Partial<CharacterConfigItem>) =>
  request.post('/map/saveCharacter', data as Record<string, unknown>)
