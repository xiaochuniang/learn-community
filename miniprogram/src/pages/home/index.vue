<template>
  <!-- 全屏虚拟地图首页 (3D Q版等距地图) -->
  <view class="map-page">
    <!-- 顶部状态栏 -->
    <view class="map-topbar" :style="{ paddingTop: safeTop + 'px' }">
      <view class="topbar-left" @tap="goProfile">
        <image class="topbar-avatar" :src="userStore.avatarUrl" mode="aspectFill" />
        <text class="topbar-name">{{ userStore.nickName }}</text>
      </view>
      <view v-if="charState.moving" class="topbar-status">
        <text class="status-going">→ {{ targetPt?.name }}</text>
        <view class="topbar-btn" @tap="togglePause">
          <text>{{ charState.paused ? '继续' : '暂停' }}</text>
        </view>
      </view>
      <view v-else-if="arrivedPt && !arrivedPt.isHome" class="topbar-status">
        <text class="status-arrived">已到 {{ arrivedPt.name }}</text>
        <view class="topbar-btn enter-btn" @tap="enterScene">
          <text>进入 ▶</text>
        </view>
      </view>
      <text v-else class="topbar-title">🗺️ 学习地图</text>
    </view>

    <!-- Canvas 地图区域 -->
    <view class="canvas-wrap" :style="{ height: canvasH + 'px' }">
      <canvas
        id="mapCanvas"
        type="2d"
        class="map-canvas"
        @touchstart.prevent="onTouchStart"
        @touchmove.prevent="onTouchMove"
        @touchend="onTouchEnd"
      />
    </view>

    <!-- 底部快捷导航栏 -->
    <view class="quick-nav">
      <scroll-view scroll-x class="quick-nav-scroll" :show-scrollbar="false">
        <view class="quick-nav-row">
          <view
            v-for="pt in navPoints"
            :key="pt.id"
            class="nav-chip"
            :class="{ 'nav-chip--active': arrivedPt?.id === pt.id }"
            @tap="() => onNavTap(pt)"
          >
            <text class="nav-chip-icon">{{ pt.icon }}</text>
            <text class="nav-chip-label">{{ pt.name }}</text>
          </view>
        </view>
      </scroll-view>
    </view>

    <!-- 点位确认弹窗 -->
    <view v-if="confirmPt" class="dialog-mask" @tap="confirmPt = null">
      <view class="dialog-card" @tap.stop>
        <text class="dialog-big-icon">{{ confirmPt.icon }}</text>
        <text class="dialog-pt-name">{{ confirmPt.name }}</text>
        <text class="dialog-pt-desc">{{ confirmPt.description }}</text>
        <view class="dialog-actions">
          <view class="dlg-cancel" @tap="confirmPt = null">取消</view>
          <view class="dlg-go" @tap="startMoveTo(confirmPt)">出发 🚀</view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useUserStore } from '@/stores/user'
import {
  getMapPoints, getCharacterConfig,
  DEFAULT_POINTS, DEFAULT_CHARACTER,
  type MapPoint, type CharacterConfig,
} from '@/api/map'

// ── Store ─────────────────────────────────────────────────────────────
const userStore = useUserStore()

// ── 系统信息 ──────────────────────────────────────────────────────────
const sys     = uni.getSystemInfoSync()
const safeTop = ref(sys.statusBarHeight ?? 20)
// 地图高度 = 窗口高 - 状态栏高 - topbar(44) - 底部导航(64) - tab bar(50)
const canvasH = ref(sys.windowHeight - (sys.statusBarHeight ?? 20) - 44 - 64 - 50)
const canvasW = ref(sys.windowWidth)

// ── 地图数据 ──────────────────────────────────────────────────────────
const mapPoints = ref<MapPoint[]>(DEFAULT_POINTS)
const charCfg   = ref<CharacterConfig>(DEFAULT_CHARACTER)

// 底部导航点（排除 home）
const navPoints = computed(() => mapPoints.value.filter(p => !p.isHome))

// ── 人物状态 ──────────────────────────────────────────────────────────
const charState = ref({
  x:         DEFAULT_CHARACTER.homeGridX as number,
  y:         DEFAULT_CHARACTER.homeGridY as number,
  moving:    false,
  paused:    false,
  walkFrame: 0 as 0 | 1 | 2 | 3,
  facing:    1 as 1 | -1,
})
const targetPt  = ref<MapPoint | null>(null)
const arrivedPt = ref<MapPoint | null>(null)
const confirmPt = ref<MapPoint | null>(null)

// ── 等距坐标系 ────────────────────────────────────────────────────────
/** 等距瓦片宽高（逻辑像素，2:1 比例） */
const TILE_W = 48
const TILE_H = 24

/** 网格坐标 → 屏幕坐标（未施加视图变换） */
function g2s(gx: number, gy: number): [number, number] {
  const ox = canvasW.value / 2
  const oy = 60
  return [
    ox + (gx - gy) * (TILE_W / 2),
    oy + (gx + gy) * (TILE_H / 2),
  ]
}

/** 屏幕坐标（触摸坐标）→ 网格坐标（用于 hit-test） */
function s2g(tx: number, ty: number): [number, number] {
  const lx = (tx - vt.x) / vt.s
  const ly = (ty - vt.y) / vt.s
  const ox = canvasW.value / 2
  const oy = 60
  const sx = lx - ox
  const sy = ly - oy
  const gx = (sx / (TILE_W / 2) + sy / (TILE_H / 2)) / 2
  const gy = (sy / (TILE_H / 2) - sx / (TILE_W / 2)) / 2
  return [gx, gy]
}

// ── 视图变换（平移 + 缩放） ───────────────────────────────────────────
/** vt.x/y = 平移偏移，vt.s = 缩放比例 */
const vt = { x: 0, y: 0, s: 1 }

// ── Canvas 引用 ───────────────────────────────────────────────────────
let canvasNode: any                      = null
let ctx: CanvasRenderingContext2D | null = null
let animId    = 0
let lastTs    = 0
let walkAccum = 0  // 走路帧计时器（秒）

/** 初始化 WeChat 小程序新版 2D Canvas */
async function initCanvas(): Promise<void> {
  return new Promise<void>(resolve => {
    uni.createSelectorQuery()
      .select('#mapCanvas')
      .fields({ node: true, size: true })
      .exec((res: any[]) => {
        if (!res[0]) { resolve(); return }
        canvasNode = res[0].node
        const dpr  = sys.pixelRatio ?? 2
        canvasNode.width  = Math.round(res[0].width  * dpr)
        canvasNode.height = Math.round(res[0].height * dpr)
        ctx = canvasNode.getContext('2d') as CanvasRenderingContext2D
        ctx.scale(dpr, dpr)
        resolve()
      })
  })
}

// ══════════════════════════════════════════════════════════════════════
// 绘制系统
// ══════════════════════════════════════════════════════════════════════

function draw() {
  if (!ctx) return
  const W = canvasW.value
  const H = canvasH.value
  ctx.clearRect(0, 0, W, H)

  ctx.save()
  ctx.translate(vt.x, vt.y)
  ctx.scale(vt.s, vt.s)

  drawBackground(W, H)
  drawPaths()
  drawDecorations()

  // 等距深度排序（gridX+gridY 小的先画 = 距视点远的先画）
  const sorted = [...mapPoints.value].sort(
    (a, b) => (a.gridX + a.gridY) - (b.gridX + b.gridY),
  )
  const charDepth = charState.value.x + charState.value.y
  let charDrawn = false
  for (const pt of sorted) {
    if (!charDrawn && charDepth < pt.gridX + pt.gridY) {
      drawCharacter()
      charDrawn = true
    }
    drawBuilding(pt)
    drawLabel(pt)
  }
  if (!charDrawn) drawCharacter()

  ctx.restore()
}

/** 草地渐变背景 + 等距网格线 */
function drawBackground(W: number, H: number) {
  if (!ctx) return
  const x0 = -vt.x / vt.s
  const y0 = -vt.y / vt.s
  const ww = W / vt.s
  const wh = H / vt.s

  const grad = ctx.createLinearGradient(x0, y0, x0, y0 + wh)
  grad.addColorStop(0, '#E8F5E9')
  grad.addColorStop(1, '#C8E6C9')
  ctx.fillStyle = grad
  ctx.fillRect(x0, y0, ww, wh)

  // 轻微等距网格纹
  ctx.strokeStyle = 'rgba(165,214,167,0.35)'
  ctx.lineWidth   = 0.5
  for (let g = 0; g <= 11; g++) {
    const [ax, ay] = g2s(g, 0); const [bx, by] = g2s(g, 11)
    ctx.beginPath(); ctx.moveTo(ax, ay); ctx.lineTo(bx, by); ctx.stroke()
    const [cx, cy] = g2s(0, g); const [dx, dy] = g2s(11, g)
    ctx.beginPath(); ctx.moveTo(cx, cy); ctx.lineTo(dx, dy); ctx.stroke()
  }
}

/** 连接点位的装饰小路（贝塞尔曲线虚线） */
function drawPaths() {
  if (!ctx) return
  const edges: [string, string][] = [
    ['home', 'library'],    ['home', 'bookstore'],
    ['home', 'stationery'], ['library', 'club'],
    ['club', 'study_room'], ['club', 'creative'],
    ['stationery', 'creative'], ['bookstore', 'library'],
  ]
  ctx.save()
  ctx.setLineDash([5, 4])
  ctx.lineWidth   = 3.5
  ctx.strokeStyle = 'rgba(255,255,255,0.72)'
  ctx.lineCap     = 'round'
  for (const [ak, bk] of edges) {
    const a = mapPoints.value.find(p => p.key === ak)
    const b = mapPoints.value.find(p => p.key === bk)
    if (!a || !b) continue
    const [ax, ay] = g2s(a.gridX, a.gridY)
    const [bx, by] = g2s(b.gridX, b.gridY)
    const ht = TILE_H / 2
    const mx = (ax + bx) / 2, my = (ay + by) / 2 - 8
    ctx.beginPath()
    ctx.moveTo(ax, ay + ht)
    ctx.quadraticCurveTo(mx, my, bx, by + ht)
    ctx.stroke()
  }
  ctx.restore()
}

/** 装饰性树木（分散在空闲格子上） */
function drawDecorations() {
  const treeGrid: [number, number][] = [
    [4,0],[0,1],[9,2],[3,6],[7,5],[0,9],[9,8],[4,5],[0,6],[5,1],[9,5],[3,10],
  ]
  for (const [gx, gy] of treeGrid) {
    const [sx, sy] = g2s(gx, gy)
    drawTree(sx, sy)
  }
}

/** Q 版树木（三层圆形树冠） */
function drawTree(sx: number, sy: number) {
  if (!ctx) return
  ctx.fillStyle = '#795548'
  ctx.fillRect(sx - 3, sy, 6, 10)
  const layers = [
    { r: 14, dy: -16, c: '#4CAF50' },
    { r: 12, dy: -26, c: '#66BB6A' },
    { r:  9, dy: -34, c: '#81C784' },
  ]
  for (const { r, dy, c } of layers) {
    ctx.beginPath()
    ctx.arc(sx, sy + dy, r, 0, Math.PI * 2)
    ctx.fillStyle = c
    ctx.fill()
    ctx.strokeStyle = 'rgba(0,0,0,0.07)'
    ctx.lineWidth   = 0.8
    ctx.stroke()
  }
}

/**
 * 绘制 Q 版等距建筑（三面立方体）
 * (sx, sy) = 该网格北顶点屏幕坐标
 */
function drawBuilding(pt: MapPoint) {
  if (!ctx) return
  const [sx, sy] = g2s(pt.gridX, pt.gridY)
  const tw = TILE_W, th = TILE_H, bh = pt.buildHeight

  // 顶面
  ctx.beginPath()
  ctx.moveTo(sx,          sy - bh)
  ctx.lineTo(sx + tw / 2, sy - bh + th / 2)
  ctx.lineTo(sx,          sy - bh + th)
  ctx.lineTo(sx - tw / 2, sy - bh + th / 2)
  ctx.closePath()
  ctx.fillStyle = pt.colorTop
  ctx.fill()
  ctx.strokeStyle = 'rgba(0,0,0,0.10)'; ctx.lineWidth = 1; ctx.stroke()

  // 左侧面
  ctx.beginPath()
  ctx.moveTo(sx - tw / 2, sy - bh + th / 2)
  ctx.lineTo(sx,          sy - bh + th)
  ctx.lineTo(sx,          sy + th)
  ctx.lineTo(sx - tw / 2, sy + th / 2)
  ctx.closePath()
  ctx.fillStyle = pt.colorLeft; ctx.fill(); ctx.stroke()

  // 右侧面（朝向观察者一侧）
  ctx.beginPath()
  ctx.moveTo(sx,          sy - bh + th)
  ctx.lineTo(sx + tw / 2, sy - bh + th / 2)
  ctx.lineTo(sx + tw / 2, sy + th / 2)
  ctx.lineTo(sx,          sy + th)
  ctx.closePath()
  ctx.fillStyle = pt.colorRight; ctx.fill(); ctx.stroke()

  // 正面装饰
  drawBuildingDeco(pt, sx, sy, tw, th, bh)
}

function drawBuildingDeco(
  pt: MapPoint, sx: number, sy: number,
  tw: number, th: number, bh: number,
) {
  if (!ctx) return
  if (pt.isHome) {
    // 家：绘制小门
    ctx.fillStyle   = '#795548'; ctx.strokeStyle = '#4E342E'; ctx.lineWidth = 1.2
    ctx.beginPath()
    ctx.roundRect(sx + tw / 6 - 5, sy + th * 0.4 - 14, 10, 14, 3)
    ctx.fill(); ctx.stroke()
    return
  }
  // 通用：两扇窗
  for (const [ox, oy] of [[-8, -4] as [number, number], [6, -4] as [number, number]]) {
    ctx.fillStyle   = 'rgba(255,235,59,0.88)'
    ctx.strokeStyle = 'rgba(0,0,0,0.18)'; ctx.lineWidth = 0.8
    ctx.beginPath()
    ctx.roundRect(sx + tw / 5 + ox, sy - bh * 0.5 + oy, 8, 6, 1)
    ctx.fill(); ctx.stroke()
  }
}

/** 建筑名称标签 */
function drawLabel(pt: MapPoint) {
  if (!ctx) return
  const [sx, sy] = g2s(pt.gridX, pt.gridY)
  const ly = sy - pt.buildHeight - 14

  ctx.save()
  ctx.shadowColor = 'rgba(0,0,0,0.26)'; ctx.shadowBlur = 5; ctx.shadowOffsetY = 2
  ctx.font = '18px sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'
  ctx.fillText(pt.icon, sx, ly - 10)
  ctx.font = 'bold 11px sans-serif'; ctx.fillStyle = '#1B5E20'
  ctx.fillText(pt.name, sx, ly + 5)
  ctx.restore()
}

/** 绘制 Q 版卡通人物（带走路帧动画） */
function drawCharacter() {
  if (!ctx) return
  const c = charState.value
  const cfg = charCfg.value
  const [sx, sy] = g2s(c.x, c.y)
  const dir = c.facing
  const swing = c.walkFrame === 1 ? 9 : c.walkFrame === 3 ? -9 : 0

  ctx.save()
  ctx.shadowColor = 'rgba(0,0,0,0.20)'; ctx.shadowBlur = 8; ctx.shadowOffsetY = 3

  // 腿部
  ctx.fillStyle = cfg.bodyColor
  for (const [lx, lm] of [[-4, 1] as [number, number], [4, -1] as [number, number]]) {
    ctx.save()
    ctx.translate(sx + lx * dir, sy + 8)
    ctx.rotate((swing * lm * Math.PI) / 180)
    ctx.fillRect(-3, 0, 6, 11)
    ctx.restore()
  }

  // 身体
  ctx.fillStyle = cfg.bodyColor
  ctx.beginPath(); ctx.roundRect(sx - 9, sy - 10, 18, 18, 5); ctx.fill()

  // 手臂
  ctx.fillStyle = cfg.skinColor
  for (const [ax2, am] of [[-9, -1] as [number, number], [9, 1] as [number, number]]) {
    ctx.save()
    ctx.translate(sx + ax2, sy - 4)
    ctx.rotate((swing * am * Math.PI) / 180)
    ctx.fillRect(ax2 < 0 ? -5 : 0, 0, 5, 10)
    ctx.restore()
  }

  // 头部
  ctx.shadowBlur = 6
  ctx.beginPath(); ctx.arc(sx, sy - 20, 12, 0, Math.PI * 2)
  ctx.fillStyle = cfg.skinColor; ctx.fill()
  ctx.strokeStyle = 'rgba(0,0,0,0.10)'; ctx.lineWidth = 1.5; ctx.stroke()

  // 头发
  ctx.beginPath(); ctx.arc(sx, sy - 26, 10, Math.PI, 0)
  ctx.fillStyle = cfg.hairColor; ctx.fill()

  // 五官
  ctx.fillStyle = '#333'
  ctx.beginPath()
  ctx.arc(sx - 4 * dir, sy - 22, 1.8, 0, Math.PI * 2)
  ctx.arc(sx + 2 * dir, sy - 22, 1.8, 0, Math.PI * 2)
  ctx.fill()
  ctx.beginPath(); ctx.arc(sx, sy - 18, 3.5, 0.15, Math.PI - 0.15)
  ctx.strokeStyle = '#555'; ctx.lineWidth = 1.5; ctx.stroke()

  // 人物名标签
  ctx.shadowBlur = 4
  ctx.font = 'bold 10px sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'
  ctx.fillStyle = '#fff'
  ctx.fillText(cfg.name, sx + 1, sy - 39)

  ctx.restore()
}

// ══════════════════════════════════════════════════════════════════════
// 动画主循环
// ══════════════════════════════════════════════════════════════════════

function startLoop() {
  const loop = (ts: number) => {
    const dt = Math.min((ts - (lastTs || ts)) / 1000, 0.1)
    lastTs = ts
    updateCharPos(dt)
    draw()
    animId = canvasNode.requestAnimationFrame(loop)
  }
  animId = canvasNode.requestAnimationFrame(loop)
}

function stopLoop() {
  if (animId && canvasNode) canvasNode.cancelAnimationFrame(animId)
}

function updateCharPos(dt: number) {
  const c = charState.value
  if (!c.moving || c.paused || !targetPt.value) return

  const tx = targetPt.value.gridX, ty = targetPt.value.gridY
  const dx = tx - c.x, dy = ty - c.y
  const dist = Math.sqrt(dx * dx + dy * dy)

  if (dist < 0.06) {
    // 到达
    c.x = tx; c.y = ty; c.moving = false; c.walkFrame = 0
    arrivedPt.value = targetPt.value; targetPt.value = null
    uni.showToast({ title: `已到达 ${arrivedPt.value!.name}`, icon: 'none', duration: 1500 })
    return
  }

  const step = Math.min(charCfg.value.walkSpeed * dt, dist)
  c.x += (dx / dist) * step
  c.y += (dy / dist) * step
  c.facing = dx > 0 ? 1 : -1

  walkAccum += dt
  if (walkAccum >= 0.18) {
    walkAccum = 0
    c.walkFrame = ((c.walkFrame + 1) % 4) as 0 | 1 | 2 | 3
  }
}

// ══════════════════════════════════════════════════════════════════════
// 手势处理
// ══════════════════════════════════════════════════════════════════════

interface TP { x: number; y: number }
let prevTouches: TP[] = []
let hasMoved = false
const TAP_THRESHOLD = 8

function onTouchStart(e: TouchEvent) {
  prevTouches = Array.from(e.touches).map(t => ({ x: t.pageX, y: t.pageY }))
  hasMoved    = false
}

function onTouchMove(e: TouchEvent) {
  const cur = Array.from(e.touches).map(t => ({ x: t.pageX, y: t.pageY }))

  if (cur.length === 1 && prevTouches.length === 1) {
    const dx = cur[0].x - prevTouches[0].x
    const dy = cur[0].y - prevTouches[0].y
    if (Math.abs(dx) > TAP_THRESHOLD || Math.abs(dy) > TAP_THRESHOLD) hasMoved = true
    vt.x = Math.max(-400, Math.min(400, vt.x + dx))
    vt.y = Math.max(-300, Math.min(300, vt.y + dy))
  } else if (cur.length >= 2 && prevTouches.length >= 2) {
    hasMoved = true
    const pd = Math.hypot(prevTouches[0].x - prevTouches[1].x, prevTouches[0].y - prevTouches[1].y)
    const cd = Math.hypot(cur[0].x - cur[1].x, cur[0].y - cur[1].y)
    if (pd > 0) {
      const ns = Math.max(0.4, Math.min(3.0, vt.s * cd / pd))
      const mx = (cur[0].x + cur[1].x) / 2
      const my = (cur[0].y + cur[1].y) / 2
      vt.x = mx - (mx - vt.x) * (ns / vt.s)
      vt.y = my - (my - vt.y) * (ns / vt.s)
      vt.s = ns
    }
  }
  prevTouches = cur
}

function onTouchEnd(e: TouchEvent) {
  if (hasMoved) return
  if (e.changedTouches.length !== 1) return

  const [gx, gy] = s2g(e.changedTouches[0].pageX, e.changedTouches[0].pageY)

  let hit: MapPoint | null = null, best = 1.5
  for (const pt of mapPoints.value) {
    const d = Math.hypot(pt.gridX - gx, pt.gridY - gy)
    if (d < best) { best = d; hit = pt }
  }
  if (!hit) return

  if (hit.isHome) { goHome() }
  else if (charState.value.moving && targetPt.value?.id === hit.id) { togglePause() }
  else { confirmPt.value = hit }
}

// ── 导航操作 ──────────────────────────────────────────────────────────

function onNavTap(pt: MapPoint) {
  if (pt.isHome) { goHome(); return }
  confirmPt.value = pt
  // 平移视图到该点
  const [sx, sy] = g2s(pt.gridX, pt.gridY)
  vt.x = canvasW.value / 2 - sx * vt.s
  vt.y = canvasH.value / 2 - sy * vt.s
}

function startMoveTo(pt: MapPoint) {
  confirmPt.value = null; targetPt.value = pt; arrivedPt.value = null
  charState.value.moving = true; charState.value.paused = false
}

function togglePause() { charState.value.paused = !charState.value.paused }

function goHome() {
  const home = mapPoints.value.find(p => p.isHome)
  if (!home) return
  targetPt.value = home; arrivedPt.value = null
  charState.value.moving = true; charState.value.paused = false
  confirmPt.value = null
}

function enterScene() {
  const path = arrivedPt.value?.targetPath
  if (path) uni.navigateTo({ url: path })
}

function goProfile() { uni.switchTab({ url: '/pages/profile/index' }) }

function resetCamera() {
  const mapW = 10 * TILE_W, mapH = 10 * TILE_H * 1.6 + 80
  vt.s = Math.min(canvasW.value / mapW * 0.88, canvasH.value / mapH * 0.80, 1.0)
  vt.x = 0; vt.y = 10
}

// ── 生命周期 ──────────────────────────────────────────────────────────

onMounted(async () => {
  await initCanvas()
  resetCamera()
  startLoop()

  try {
    const [ptsRes, cfgRes] = await Promise.allSettled([getMapPoints(), getCharacterConfig()])
    if (ptsRes.status === 'fulfilled' && ptsRes.value.length) {
      mapPoints.value = ptsRes.value
      const home = ptsRes.value.find(p => p.isHome)
      if (home) { charState.value.x = home.gridX; charState.value.y = home.gridY }
    }
    if (cfgRes.status === 'fulfilled') {
      charCfg.value = cfgRes.value
      charState.value.x = cfgRes.value.homeGridX
      charState.value.y = cfgRes.value.homeGridY
    }
  } catch { /* use defaults */ }

  arrivedPt.value = mapPoints.value.find(p => p.isHome) ?? null
})

onUnmounted(() => { stopLoop() })
</script>

<style lang="scss" scoped>
.map-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #E8F5E9;
  overflow: hidden;
  position: relative;
}

/* 顶部状态栏 */
.map-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28rpx 14rpx;
  background: linear-gradient(135deg, #43a047, #1b5e20);
  min-height: 44px;
  box-shadow: 0 4rpx 16rpx rgba(0,0,0,0.20);
  z-index: 10;
  flex-shrink: 0;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 12rpx;
  max-width: 200rpx;
}

.topbar-avatar {
  width: 56rpx;
  height: 56rpx;
  border-radius: 50%;
  border: 3rpx solid rgba(255,255,255,0.75);
  flex-shrink: 0;
}

.topbar-name {
  font-size: 26rpx;
  color: #fff;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.topbar-title {
  font-size: 30rpx;
  color: #fff;
  font-weight: 700;
}

.topbar-status {
  display: flex;
  align-items: center;
  gap: 16rpx;
  flex: 1;
  justify-content: flex-end;
}

.status-going   { font-size: 24rpx; color: #FFF9C4; }
.status-arrived { font-size: 24rpx; color: #B9F6CA; }

.topbar-btn {
  padding: 6rpx 20rpx;
  border-radius: 30rpx;
  background: rgba(255,255,255,0.22);
  border: 1.5rpx solid rgba(255,255,255,0.45);
  font-size: 22rpx;
  color: #fff;
  white-space: nowrap;
  flex-shrink: 0;

  &.enter-btn {
    background: #FFE082;
    color: #5D4037;
    border-color: #FFD54F;
  }
}

/* Canvas 区域 */
.canvas-wrap {
  flex: 1;
  position: relative;
  overflow: hidden;
}

.map-canvas {
  width: 100%;
  height: 100%;
  display: block;
}

/* 底部快捷导航 */
.quick-nav {
  height: 128rpx;
  background: rgba(255,255,255,0.96);
  box-shadow: 0 -4rpx 16rpx rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.quick-nav-scroll { width: 100%; height: 100%; }

.quick-nav-row {
  display: flex;
  gap: 16rpx;
  padding: 16rpx 24rpx;
  height: 100%;
  box-sizing: border-box;
  align-items: center;
}

.nav-chip {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4rpx;
  padding: 12rpx 20rpx;
  border-radius: 20rpx;
  background: #F1F8E9;
  border: 2rpx solid transparent;

  &--active { background: #DCEDC8; border-color: #43a047; }
}

.nav-chip-icon  { font-size: 32rpx; }
.nav-chip-label { font-size: 20rpx; color: #2E7D32; font-weight: 500; white-space: nowrap; }

/* 确认弹窗 */
.dialog-mask {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.48);
  display: flex;
  align-items: flex-end;
  z-index: 100;
}

.dialog-card {
  width: 100%;
  background: #fff;
  border-radius: 40rpx 40rpx 0 0;
  padding: 48rpx 48rpx 100rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20rpx;
}

.dialog-big-icon { font-size: 88rpx; line-height: 1; }
.dialog-pt-name  { font-size: 42rpx; font-weight: 700; color: #1B5E20; }
.dialog-pt-desc  { font-size: 28rpx; color: #558B2F; text-align: center; }

.dialog-actions {
  display: flex;
  gap: 24rpx;
  margin-top: 8rpx;
  width: 100%;
}

.dlg-cancel {
  flex: 1;
  height: 88rpx;
  border-radius: 44rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32rpx;
  color: #666;
  background: #F5F5F5;
}

.dlg-go {
  flex: 2;
  height: 88rpx;
  border-radius: 44rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32rpx;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #66BB6A, #1B5E20);
  box-shadow: 0 8rpx 28rpx rgba(27,94,32,0.32);
}
</style>
