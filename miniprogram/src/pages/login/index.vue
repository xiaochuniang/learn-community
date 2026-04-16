<template>
  <view class="login-page">
    <!-- 背景渐变 -->
    <view class="bg-gradient" />

    <!-- Logo 区 -->
    <view class="logo-area">
      <image src="/static/images/logo.png" class="logo" mode="aspectFit" />
      <text class="app-name">学习社区</text>
      <text class="app-slogan">专注青少年成长，陪伴每一步学习</text>
    </view>

    <!-- 登录卡片 -->
    <view class="login-card">
      <text class="card-title">欢迎加入</text>
      <text class="card-desc">使用微信账号一键登录，享受完整学习体验</text>

      <!-- 微信一键登录按钮 -->
      <button
        class="btn-wx-login"
        open-type="getPhoneNumber"
        :loading="userStore.loading"
        @getphonenumber="onGetPhoneNumber"
      >
        <image src="/static/images/wx-logo.png" class="wx-icon" mode="aspectFit" />
        <text>微信一键登录</text>
      </button>

      <!-- 游客模式 -->
      <text class="guest-link" @tap="goGuest">暂不登录，以游客身份浏览</text>
    </view>

    <!-- 用户协议提示 -->
    <view class="agreement-tip">
      <text>登录即代表您同意</text>
      <text class="link" @tap="openDoc('privacy')">《隐私政策》</text>
      <text>和</text>
      <text class="link" @tap="openDoc('terms')">《用户协议》</text>
    </view>
  </view>
</template>

<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { useUserStore } from '@/stores/user'
import { wxLogin } from '@/api/auth'
import { storage } from '@/utils/storage'

const userStore = useUserStore()

// 登录成功后的跳转地址（由 auth 中转页传入）
let redirectUrl = '/pages/home/index'

onLoad((options: Record<string, string>) => {
  if (options?.redirect) {
    redirectUrl = decodeURIComponent(options.redirect)
  }
  // 如果已登录，直接跳转
  if (userStore.isLoggedIn) {
    goRedirect()
  }
})

/** 获取手机号回调（微信授权）*/
async function onGetPhoneNumber(e: WechatMiniprogram.GetPhoneNumberEvent) {
  if (e.detail.errMsg !== 'getPhoneNumber:ok') {
    uni.showToast({ title: '已取消授权', icon: 'none' })
    return
  }

  try {
    // 先拿 code
    const { code } = await uni.login({ provider: 'weixin' })
    const res = await wxLogin(code)
    storage.setToken(res.data.token)
    userStore.token = res.data.token
    await userStore.fetchUserInfo()

    uni.showToast({ title: '登录成功', icon: 'success' })
    setTimeout(() => goRedirect(), 1200)
  } catch (err) {
    console.error('[Login] 登录失败:', err)
    uni.showToast({ title: '登录失败，请重试', icon: 'none' })
  }
}

function goGuest() {
  uni.showModal({
    title: '提示',
    content: '游客模式下部分功能不可用，建议登录后使用',
    confirmText: '继续游览',
    cancelText: '去登录',
    success: (res) => {
      if (res.confirm) goRedirect()
    }
  })
}

function goRedirect() {
  if (redirectUrl.includes('tabBar') || redirectUrl === '/pages/home/index') {
    uni.switchTab({ url: redirectUrl })
  } else {
    uni.redirectTo({ url: redirectUrl })
  }
}

function openDoc(type: 'privacy' | 'terms') {
  const map = { privacy: '/pages/webview/index?url=privacy', terms: '/pages/webview/index?url=terms' }
  uni.navigateTo({ url: map[type] })
}
</script>

<style lang="scss" scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #f4f6f8;
  position: relative;
  overflow: hidden;
}

.bg-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 480rpx;
  background: linear-gradient(145deg, #3b82f6 0%, #6366f1 100%);
  border-radius: 0 0 80rpx 80rpx;
}

.logo-area {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 120rpx;
  gap: 16rpx;

  .logo {
    width: 120rpx;
    height: 120rpx;
    border-radius: 24rpx;
    background: rgba(255,255,255,0.15);
  }

  .app-name {
    font-size: 44rpx;
    font-weight: 700;
    color: #fff;
    letter-spacing: 4rpx;
  }

  .app-slogan {
    font-size: 26rpx;
    color: rgba(255,255,255,0.85);
  }
}

.login-card {
  position: relative;
  z-index: 1;
  background: #fff;
  border-radius: 24rpx;
  padding: 56rpx 48rpx;
  margin: 48rpx 32rpx 0;
  width: calc(100% - 64rpx);
  box-sizing: border-box;
  box-shadow: 0 8rpx 40rpx rgba(0,0,0,0.10);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24rpx;

  .card-title {
    font-size: 38rpx;
    font-weight: 700;
    color: #1a1a2e;
  }

  .card-desc {
    font-size: 26rpx;
    color: #8a8fa8;
    text-align: center;
    line-height: 1.6;
  }
}

.btn-wx-login {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16rpx;
  width: 100%;
  height: 96rpx;
  background: #07c160;
  color: #fff;
  font-size: 32rpx;
  font-weight: 600;
  border-radius: 48rpx;
  border: none;
  margin-top: 16rpx;

  &::after { border: none; }

  .wx-icon {
    width: 44rpx;
    height: 44rpx;
  }
}

.guest-link {
  font-size: 26rpx;
  color: #a0a4b8;
  text-decoration: underline;
  padding: 8rpx 0;
}

.agreement-tip {
  position: relative;
  z-index: 1;
  margin-top: 40rpx;
  font-size: 22rpx;
  color: #a0a4b8;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 4rpx;

  .link {
    color: $primary-color;
  }
}
</style>
