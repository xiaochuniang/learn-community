<template>
  <view class="auth-page">
    <u-loading-icon size="32" text="正在验证登录状态..." />
  </view>
</template>

<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'
import { useUserStore } from '@/stores/user'

onLoad(async (options: Record<string, string>) => {
  const userStore = useUserStore()
  const redirect = options?.redirect ? decodeURIComponent(options.redirect) : '/pages/home/index'

  if (userStore.isLoggedIn) {
    // 已登录，直接跳
    goPage(redirect)
    return
  }

  // 尝试静默登录（从 storage 恢复）
  await userStore.initFromStorage()

  if (userStore.isLoggedIn) {
    goPage(redirect)
  } else {
    // 跳转登录页，带回调
    uni.redirectTo({ url: `/pages/login/index?redirect=${encodeURIComponent(redirect)}` })
  }
})

function goPage(url: string) {
  const tabPages = ['/pages/home/index', '/pages/course/index', '/pages/message/index', '/pages/profile/index']
  if (tabPages.includes(url)) {
    uni.switchTab({ url })
  } else {
    uni.redirectTo({ url })
  }
}
</script>

<style lang="scss" scoped>
.auth-page {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background: #fff;
}
</style>
