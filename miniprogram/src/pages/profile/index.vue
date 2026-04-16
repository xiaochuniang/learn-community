<template>
  <view class="profile-page">
    <!-- 用户信息头部 -->
    <view class="profile-header">
      <view class="user-info" v-if="userStore.isLoggedIn">
        <image :src="userStore.avatarUrl" class="avatar" mode="aspectFill" />
        <view class="user-meta">
          <text class="nick-name">{{ userStore.nickName }}</text>
          <text class="user-grade">{{ userStore.userInfo?.grade || '暂未填写年级' }}</text>
          <view class="vip-badge" v-if="userStore.isVip">
            <u-icon name="vip-circle-fill" size="14" color="#f7ba2a" />
            <text>VIP会员</text>
          </view>
        </view>
        <u-icon name="arrow-right" size="18" color="rgba(255,255,255,0.7)" @tap="goEditProfile" />
      </view>

      <view class="user-info not-login" v-else @tap="goLogin">
        <view class="avatar-placeholder">
          <u-icon name="account" size="36" color="#fff" />
        </view>
        <view class="user-meta">
          <text class="nick-name">点击登录</text>
          <text class="user-grade">登录后享受完整功能</text>
        </view>
        <u-icon name="arrow-right" size="18" color="rgba(255,255,255,0.7)" />
      </view>

      <!-- 数据统计 -->
      <view class="stats-row">
        <view class="stat-item" @tap="goStudyRecord">
          <text class="stat-num">{{ stats.studyDays }}</text>
          <text class="stat-label">学习天数</text>
        </view>
        <view class="stat-divider" />
        <view class="stat-item" @tap="goPoints">
          <text class="stat-num">{{ userStore.userInfo?.points ?? 0 }}</text>
          <text class="stat-label">积分</text>
        </view>
        <view class="stat-divider" />
        <view class="stat-item" @tap="goOrders">
          <text class="stat-num">{{ stats.orderCount }}</text>
          <text class="stat-label">订单</text>
        </view>
        <view class="stat-divider" />
        <view class="stat-item" @tap="goFavorites">
          <text class="stat-num">{{ stats.favoriteCount }}</text>
          <text class="stat-label">收藏</text>
        </view>
      </view>
    </view>

    <!-- 我的订单 -->
    <view class="section">
      <view class="section-title-row">
        <text class="section-title">我的订单</text>
        <text class="section-more" @tap="goOrders">全部 ›</text>
      </view>
      <view class="order-tab-row">
        <view
          v-for="tab in orderTabs"
          :key="tab.key"
          class="order-tab"
          @tap="goOrders(tab.key)"
        >
          <u-icon :name="tab.icon" size="28" color="#3b82f6" />
          <text>{{ tab.label }}</text>
        </view>
      </view>
    </view>

    <!-- 功能菜单 -->
    <view class="menu-section">
      <view
        v-for="item in menuItems"
        :key="item.key"
        class="menu-item"
        @tap="() => goMenu(item)"
      >
        <view class="menu-left">
          <view class="menu-icon" :style="{ background: item.bg }">
            <u-icon :name="item.icon" size="20" :color="item.color" />
          </view>
          <text class="menu-label">{{ item.label }}</text>
        </view>
        <u-icon name="arrow-right" size="16" color="#c0c4cc" />
      </view>
    </view>

    <!-- 退出登录 -->
    <view v-if="userStore.isLoggedIn" class="logout-btn" @tap="handleLogout">
      退出登录
    </view>

    <view style="height: 48rpx" />
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { useUserStore } from '@/stores/user'
import request from '@/utils/request'

const userStore = useUserStore()

interface Stats {
  studyDays: number
  orderCount: number
  favoriteCount: number
}

const stats = ref<Stats>({ studyDays: 0, orderCount: 0, favoriteCount: 0 })

onShow(async () => {
  if (userStore.isLoggedIn) {
    await userStore.fetchUserInfo()
    const res = await request.get<Stats>('/user/stats').catch(() => null)
    if (res) stats.value = res.data
  }
})

const orderTabs = [
  { key: 'pending_pay', label: '待付款', icon: 'order' },
  { key: 'pending_start', label: '待开始', icon: 'clock' },
  { key: 'in_progress', label: '进行中', icon: 'play-right' },
  { key: 'finished', label: '已完成', icon: 'checkmark-circle' }
]

const menuItems = [
  { key: 'tutor-apply', label: '成为陪伴师', icon: 'account-fill', bg: '#eff6ff', color: '#3b82f6', path: '/pages/tutor/apply' },
  { key: 'favorites', label: '我的收藏', icon: 'star', bg: '#fef9c3', color: '#eab308', path: '/pages/profile/favorites' },
  { key: 'study-record', label: '学习记录', icon: 'clock', bg: '#f0fdf4', color: '#22c55e', path: '/pages/profile/study-record' },
  { key: 'points', label: '积分中心', icon: 'gift', bg: '#fdf2f8', color: '#ec4899', path: '/pages/profile/points' },
  { key: 'setting', label: '设置', icon: 'setting', bg: '#f8faff', color: '#6366f1', path: '/pages/profile/setting' },
  { key: 'feedback', label: '意见反馈', icon: 'chat', bg: '#fff7ed', color: '#f97316', path: '/pages/profile/feedback' },
  { key: 'about', label: '关于我们', icon: 'info-circle', bg: '#f9f9f9', color: '#909399', path: '/pages/profile/about' }
]

function goLogin() { uni.navigateTo({ url: '/pages/login/index' }) }
function goEditProfile() { uni.navigateTo({ url: '/pages/profile/edit' }) }
function goPoints() { uni.navigateTo({ url: '/pages/profile/points' }) }
function goOrders(status?: string) {
  const url = status ? `/pages/order/index?status=${status}` : '/pages/order/index'
  uni.navigateTo({ url })
}
function goFavorites() { uni.navigateTo({ url: '/pages/profile/favorites' }) }
function goStudyRecord() { uni.navigateTo({ url: '/pages/profile/study-record' }) }

function goMenu(item: { path: string }) {
  if (!userStore.isLoggedIn) {
    uni.navigateTo({ url: '/pages/login/index' })
    return
  }
  uni.navigateTo({ url: item.path })
}

async function handleLogout() {
  uni.showModal({
    title: '提示',
    content: '确认退出登录吗？',
    success: async (res) => {
      if (res.confirm) await userStore.logout()
    }
  })
}
</script>

<style lang="scss" scoped>
.profile-page {
  background: #f4f6f8;
  min-height: 100vh;
}

/* 头部 */
.profile-header {
  background: linear-gradient(145deg, #3b82f6, #6366f1);
  padding: 60rpx 32rpx 0;
  border-radius: 0 0 40rpx 40rpx;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 24rpx;
  padding-bottom: 32rpx;
}

.avatar {
  width: 120rpx;
  height: 120rpx;
  border-radius: 50%;
  border: 4rpx solid rgba(255,255,255,0.5);
}

.avatar-placeholder {
  width: 120rpx;
  height: 120rpx;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.nick-name {
  font-size: 36rpx;
  font-weight: 700;
  color: #fff;
}

.user-grade { font-size: 24rpx; color: rgba(255,255,255,0.8); }

.vip-badge {
  display: inline-flex;
  align-items: center;
  gap: 6rpx;
  background: rgba(247,186,42,0.15);
  border: 1rpx solid #f7ba2a;
  padding: 4rpx 16rpx;
  border-radius: 20rpx;
  font-size: 22rpx;
  color: #f7ba2a;
  align-self: flex-start;
}

.stats-row {
  display: flex;
  background: rgba(255,255,255,0.12);
  border-radius: 16rpx;
  margin-bottom: 24rpx;
  padding: 20rpx 0;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6rpx;
}

.stat-num {
  font-size: 36rpx;
  font-weight: 700;
  color: #fff;
}

.stat-label { font-size: 22rpx; color: rgba(255,255,255,0.8); }

.stat-divider {
  width: 1rpx;
  background: rgba(255,255,255,0.3);
  margin: 8rpx 0;
}

/* 订单 section */
.section {
  margin: 20rpx 24rpx 0;
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.04);
}

.section-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20rpx;
}

.section-title {
  font-size: 30rpx;
  font-weight: 700;
  color: #1a1a2e;
}

.section-more { font-size: 24rpx; color: $primary-color; }

.order-tab-row {
  display: flex;
  justify-content: space-between;
}

.order-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8rpx;
  font-size: 22rpx;
  color: #606266;
  flex: 1;
}

/* 功能菜单 */
.menu-section {
  margin: 20rpx 24rpx 0;
  background: #fff;
  border-radius: 20rpx;
  overflow: hidden;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.04);
}

.menu-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 28rpx 28rpx;
  border-bottom: 1rpx solid #f5f5f5;

  &:last-child { border-bottom: none; }
}

.menu-left {
  display: flex;
  align-items: center;
  gap: 20rpx;
}

.menu-icon {
  width: 64rpx;
  height: 64rpx;
  border-radius: 16rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.menu-label { font-size: 28rpx; color: #303133; }

/* 退出按钮 */
.logout-btn {
  margin: 32rpx 24rpx 0;
  height: 88rpx;
  line-height: 88rpx;
  text-align: center;
  background: #fff;
  border-radius: 20rpx;
  font-size: 30rpx;
  color: #ef4444;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.04);
}
</style>
