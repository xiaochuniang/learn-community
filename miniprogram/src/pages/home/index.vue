<template>
  <view class="home-page">
    <!-- 顶部搜索栏 -->
    <view class="search-bar">
      <view class="search-input" @tap="goSearch">
        <u-icon name="search" size="18" color="#aaa" />
        <text class="search-placeholder">搜索课程、陪伴师…</text>
      </view>
      <view class="notice-btn" @tap="goNotice">
        <u-icon name="bell" size="22" color="#fff" />
        <view v-if="unreadCount > 0" class="badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</view>
      </view>
    </view>

    <!-- Banner 轮播 -->
    <view class="banner-wrap">
      <u-swiper
        :list="banners"
        keyName="imageUrl"
        :autoplay="true"
        :interval="4000"
        radius="16"
        height="360rpx"
        bgColor="#dce8ff"
        @click="onBannerClick"
      />
    </view>

    <!-- 快捷入口 -->
    <view class="quick-entry">
      <view
        v-for="item in quickEntries"
        :key="item.key"
        class="entry-item"
        @tap="() => goEntry(item)"
      >
        <view class="entry-icon" :style="{ background: item.bg }">
          <u-icon :name="item.icon" size="28" :color="item.color" />
        </view>
        <text class="entry-label">{{ item.label }}</text>
      </view>
    </view>

    <!-- 公告栏 -->
    <view v-if="notices.length" class="notice-bar">
      <u-icon name="volume" size="16" color="#3b82f6" />
      <u-notice-bar :text="noticeText" :volume="false" color="#3b82f6" bgColor="#eff6ff" />
    </view>

    <!-- 推荐课程 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">推荐课程</text>
        <text class="section-more" @tap="goCourseList">查看全部 ›</text>
      </view>
      <scroll-view scroll-x class="course-scroll">
        <view class="course-list">
          <view
            v-for="course in courses"
            :key="course.id"
            class="course-card"
            @tap="goCourseDetail(course.id)"
          >
            <image :src="course.coverUrl" class="course-cover" mode="aspectFill" />
            <view class="course-info">
              <text class="course-title">{{ course.title }}</text>
              <text class="course-teacher">{{ course.teacherName }}</text>
              <view class="course-footer">
                <text class="course-price" :class="{ free: course.isFree }">
                  {{ course.isFree ? '免费' : `¥ ${course.price}` }}
                </text>
                <text class="course-students">{{ course.studentCount }}人学习</text>
              </view>
            </view>
          </view>
        </view>
      </scroll-view>
    </view>

    <!-- 推荐陪伴师 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">精选陪伴师</text>
        <text class="section-more" @tap="goTutorList">查看全部 ›</text>
      </view>
      <view class="tutor-grid">
        <view
          v-for="tutor in tutors"
          :key="tutor.id"
          class="tutor-card"
          @tap="goTutorDetail(tutor.id)"
        >
          <image :src="tutor.avatar" class="tutor-avatar" mode="aspectFill" />
          <text class="tutor-name">{{ tutor.realName }}</text>
          <text class="tutor-school">{{ tutor.school }}</text>
          <view class="tutor-tags">
            <text v-for="tag in tutor.tags.slice(0,2)" :key="tag" class="tutor-tag">{{ tag }}</text>
          </view>
          <view class="tutor-footer">
            <text class="tutor-price">¥{{ tutor.servicePrice }}/时</text>
            <view class="tutor-score">
              <u-icon name="star-fill" size="12" color="#f7ba2a" />
              <text>{{ tutor.score }}</text>
            </view>
          </view>
        </view>
      </view>
    </view>

    <!-- 底部占位 -->
    <view style="height: 32rpx;" />
  </view>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { onPullDownRefresh } from '@dcloudio/uni-app'
import { getHomeBanners, getRecommendCourses, getRecommendTutors, getNotices } from '@/api/home'
import type { BannerItem, CourseItem, TutorItem, NoticeItem } from '@/api/home'

// ── 数据 ─────────────────────────────────────────────────────────
const banners = ref<BannerItem[]>([])
const courses = ref<CourseItem[]>([])
const tutors = ref<TutorItem[]>([])
const notices = ref<NoticeItem[]>([])
const unreadCount = ref(0)

const noticeText = computed(() => notices.value.map((n) => n.title).join('    '))

// ── 快捷入口 ─────────────────────────────────────────────────────
const quickEntries = [
  { key: 'course', label: '精品课程', icon: 'book', bg: '#eff6ff', color: '#3b82f6', path: '/pages/course/index' },
  { key: 'tutor', label: '陪伴师', icon: 'account', bg: '#f0fdf4', color: '#22c55e', path: '/pages/tutor/index' },
  { key: 'order', label: '我的订单', icon: 'order', bg: '#fef9c3', color: '#eab308', path: '/pages/order/index' },
  { key: 'points', label: '积分中心', icon: 'gift', bg: '#fdf2f8', color: '#ec4899', path: '/pages/profile/points' }
]

// ── 接口请求 ─────────────────────────────────────────────────────
async function loadData() {
  const [b, c, t, n] = await Promise.allSettled([
    getHomeBanners(),
    getRecommendCourses(6),
    getRecommendTutors(4),
    getNotices()
  ])
  if (b.status === 'fulfilled') banners.value = b.value.data
  if (c.status === 'fulfilled') courses.value = c.value.data
  if (t.status === 'fulfilled') tutors.value = t.value.data
  if (n.status === 'fulfilled') notices.value = n.value.data
}

onMounted(loadData)

onPullDownRefresh(async () => {
  await loadData()
  uni.stopPullDownRefresh()
})

// ── 导航 ─────────────────────────────────────────────────────────
function onBannerClick(index: number) {
  const item = banners.value[index]
  if (!item) return
  if (item.linkType === 'course') goCourseDetail(item.linkId)
  else if (item.linkType === 'tutor') goTutorDetail(item.linkId)
  else if (item.linkType === 'external') uni.navigateTo({ url: `/pages/webview/index?url=${encodeURIComponent(item.linkUrl)}` })
}

function goEntry(item: { path: string }) {
  uni.navigateTo({ url: item.path })
}

function goSearch() { uni.navigateTo({ url: '/pages/search/index' }) }
function goNotice() { uni.navigateTo({ url: '/pages/message/index' }) }
function goCourseList() { uni.switchTab({ url: '/pages/course/index' }) }
function goCourseDetail(id: number) { uni.navigateTo({ url: `/pages/course-detail/index?id=${id}` }) }
function goTutorList() { uni.navigateTo({ url: '/pages/tutor/index' }) }
function goTutorDetail(id: number) { uni.navigateTo({ url: `/pages/tutor/detail?id=${id}` }) }
</script>

<style lang="scss" scoped>
.home-page {
  background: #f4f6f8;
  min-height: 100vh;
}

/* 搜索栏 */
.search-bar {
  display: flex;
  align-items: center;
  gap: 20rpx;
  padding: 24rpx 32rpx;
  background: $primary-color;
}

.search-input {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12rpx;
  background: rgba(255,255,255,0.92);
  border-radius: 40rpx;
  padding: 14rpx 24rpx;
}

.search-placeholder {
  font-size: 28rpx;
  color: #aaa;
}

.notice-btn {
  position: relative;
  width: 56rpx;
  height: 56rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.badge {
  position: absolute;
  top: -4rpx;
  right: -4rpx;
  background: #ef4444;
  color: #fff;
  font-size: 18rpx;
  padding: 2rpx 8rpx;
  border-radius: 20rpx;
  min-width: 28rpx;
  text-align: center;
}

/* Banner */
.banner-wrap {
  margin: 24rpx 32rpx 0;
}

/* 快捷入口 */
.quick-entry {
  display: flex;
  justify-content: space-between;
  padding: 28rpx 32rpx;
}

.entry-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12rpx;
}

.entry-icon {
  width: 96rpx;
  height: 96rpx;
  border-radius: 28rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.entry-label {
  font-size: 24rpx;
  color: #606266;
}

/* 公告栏 */
.notice-bar {
  margin: 0 32rpx 16rpx;
  background: #eff6ff;
  border-radius: 12rpx;
  padding: 0 20rpx;
  display: flex;
  align-items: center;
  overflow: hidden;
}

/* Section 通用 */
.section {
  margin: 16rpx 32rpx;
  background: #fff;
  border-radius: 20rpx;
  padding: 28rpx 28rpx 12rpx;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.04);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: 700;
  color: #1a1a2e;
}

.section-more {
  font-size: 24rpx;
  color: $primary-color;
}

/* 课程横向滚动 */
.course-scroll { margin: 0 -28rpx; }

.course-list {
  display: flex;
  gap: 20rpx;
  padding: 4rpx 28rpx 20rpx;
}

.course-card {
  flex-shrink: 0;
  width: 280rpx;
  background: #f8faff;
  border-radius: 16rpx;
  overflow: hidden;
}

.course-cover {
  width: 280rpx;
  height: 160rpx;
}

.course-info {
  padding: 16rpx;
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.course-title {
  font-size: 26rpx;
  font-weight: 600;
  color: #1a1a2e;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-teacher { font-size: 22rpx; color: #8a8fa8; }

.course-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.course-price {
  font-size: 28rpx;
  font-weight: 700;
  color: #ef4444;

  &.free { color: #22c55e; }
}

.course-students { font-size: 20rpx; color: #aaa; }

/* 陪伴师 2列网格 */
.tutor-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20rpx;
  padding-bottom: 12rpx;
}

.tutor-card {
  background: #f8faff;
  border-radius: 16rpx;
  padding: 20rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10rpx;
}

.tutor-avatar {
  width: 100rpx;
  height: 100rpx;
  border-radius: 50%;
}

.tutor-name { font-size: 28rpx; font-weight: 600; color: #1a1a2e; }
.tutor-school { font-size: 22rpx; color: #8a8fa8; }

.tutor-tags {
  display: flex;
  gap: 8rpx;
  flex-wrap: wrap;
  justify-content: center;
}

.tutor-tag {
  font-size: 20rpx;
  color: $primary-color;
  background: #eff6ff;
  padding: 4rpx 12rpx;
  border-radius: 20rpx;
}

.tutor-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.tutor-price { font-size: 26rpx; font-weight: 700; color: #ef4444; }

.tutor-score {
  display: flex;
  align-items: center;
  gap: 4rpx;
  font-size: 22rpx;
  color: #f7ba2a;
}
</style>
