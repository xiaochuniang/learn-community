<script setup lang="ts">
import { onLaunch, onShow, onHide } from '@dcloudio/uni-app'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()

onLaunch(async () => {
  console.log('[App] onLaunch')
  // 启动时静默恢复登录态
  await userStore.initFromStorage()
})

onShow(() => {
  console.log('[App] onShow')
})

onHide(() => {
  console.log('[App] onHide')
})
</script>

<style lang="scss">
/* 引入 uView Plus 基础样式 */
@import 'uview-plus/index.scss';
/* 引入项目全局样式 */
@import '@/styles/index.scss';
</style>
