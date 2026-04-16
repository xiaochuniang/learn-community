import request from '@/utils/request'

// ── 类型定义 ────────────────────────────────────────────────────────

/** 地图点位（服务端格式，下划线命名） */
export interface MapPointRaw {
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
}

/** 前端使用的地图点位（camelCase） */
export interface MapPoint {
  id: number
  key: string
  name: string
  icon: string
  description: string
  gridX: number
  gridY: number
  buildHeight: number
  colorTop: string
  colorLeft: string
  colorRight: string
  targetPath: string
  isHome: boolean
  sort: number
}

/** 卡通人物配置 */
export interface CharacterConfig {
  id: number
  name: string
  bodyColor: string
  skinColor: string
  hairColor: string
  walkSpeed: number
  homeGridX: number
  homeGridY: number
}

// ── 格式转换 ──────────────────────────────────────────────────────────

export function toMapPoint(raw: MapPointRaw): MapPoint {
  return {
    id:          raw.id,
    key:         raw.key,
    name:        raw.name,
    icon:        raw.icon,
    description: raw.description,
    gridX:       raw.grid_x,
    gridY:       raw.grid_y,
    buildHeight: raw.build_height,
    colorTop:    raw.color_top,
    colorLeft:   raw.color_left,
    colorRight:  raw.color_right,
    targetPath:  raw.target_path,
    isHome:      raw.is_home === 1,
    sort:        raw.sort,
  }
}

function toCharacterConfig(raw: Record<string, unknown>): CharacterConfig {
  return {
    id:          Number(raw['id']           ?? 0),
    name:        String(raw['name']         ?? '小明'),
    bodyColor:   String(raw['body_color']   ?? '#FF9800'),
    skinColor:   String(raw['skin_color']   ?? '#FFCC80'),
    hairColor:   String(raw['hair_color']   ?? '#5D4037'),
    walkSpeed:   Number(raw['walk_speed']   ?? 2),
    homeGridX:   Number(raw['home_grid_x']  ?? 2),
    homeGridY:   Number(raw['home_grid_y']  ?? 8),
  }
}

// ── API 请求 ─────────────────────────────────────────────────────────

/** 获取全部启用地图点位 */
export async function getMapPoints(): Promise<MapPoint[]> {
  const res = await request.get<MapPointRaw[]>('/map/points')
  return (res.data || []).map(toMapPoint)
}

/** 获取卡通人物配置 */
export async function getCharacterConfig(): Promise<CharacterConfig> {
  const res = await request.get<Record<string, unknown>>('/map/character')
  return toCharacterConfig(res.data || {})
}

// ── 本地默认数据（离线 / 接口失败时的兜底） ──────────────────────────

export const DEFAULT_POINTS: MapPoint[] = [
  {
    id: 1, key: 'home', name: '自己家', icon: '🏠',
    description: '温暖的出发地', gridX: 2, gridY: 8, buildHeight: 52,
    colorTop: '#FFE066', colorLeft: '#E8C84A', colorRight: '#C8A825',
    targetPath: '', isHome: true, sort: 100,
  },
  {
    id: 2, key: 'library', name: '图书馆', icon: '📚',
    description: '知识的殿堂，借阅海量书目', gridX: 2, gridY: 2, buildHeight: 72,
    colorTop: '#87CEEB', colorLeft: '#5BA3D0', colorRight: '#3A7DB8',
    targetPath: '/pages/map/library', isHome: false, sort: 90,
  },
  {
    id: 3, key: 'study_room', name: '自习室', icon: '✏️',
    description: '专注学习，番茄钟陪伴', gridX: 8, gridY: 2, buildHeight: 64,
    colorTop: '#B39DDB', colorLeft: '#7E57C2', colorRight: '#512DA8',
    targetPath: '/pages/study/room', isHome: false, sort: 80,
  },
  {
    id: 4, key: 'creative', name: '文创店', icon: '🎨',
    description: '文创周边，激发创意灵感', gridX: 8, gridY: 7, buildHeight: 56,
    colorTop: '#FFAB76', colorLeft: '#E87B3A', colorRight: '#C65A1A',
    targetPath: '/pages/map/creative', isHome: false, sort: 70,
  },
  {
    id: 5, key: 'stationery', name: '文具店', icon: '🖊️',
    description: '学习文具一站齐', gridX: 5, gridY: 9, buildHeight: 52,
    colorTop: '#A5D6A7', colorLeft: '#66BB6A', colorRight: '#388E3C',
    targetPath: '/pages/map/stationery', isHome: false, sort: 60,
  },
  {
    id: 6, key: 'bookstore', name: '书店', icon: '📖',
    description: '精选好书，发现阅读乐趣', gridX: 1, gridY: 5, buildHeight: 60,
    colorTop: '#EF9A9A', colorLeft: '#E57373', colorRight: '#C62828',
    targetPath: '/pages/map/bookstore', isHome: false, sort: 50,
  },
  {
    id: 7, key: 'club', name: '社团', icon: '🎭',
    description: '加入社团，结交志同道合的朋友', gridX: 6, gridY: 4, buildHeight: 60,
    colorTop: '#80DEEA', colorLeft: '#4DD0E1', colorRight: '#0097A7',
    targetPath: '/pages/map/club', isHome: false, sort: 40,
  },
]

export const DEFAULT_CHARACTER: CharacterConfig = {
  id: 0, name: '小明',
  bodyColor: '#FF9800', skinColor: '#FFCC80', hairColor: '#5D4037',
  walkSpeed: 2, homeGridX: 2, homeGridY: 8,
}
