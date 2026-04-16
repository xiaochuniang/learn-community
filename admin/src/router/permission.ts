import router, { whiteList } from './index'
import { useUserStore } from '@/store/user'
import { useTabsStore } from '@/store/tabs'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

NProgress.configure({ showSpinner: false })

router.beforeEach(async (to, _from, next) => {
  NProgress.start()

  const userStore = useUserStore()

  // 白名单直接放行
  if (whiteList.includes(to.path)) {
    if (userStore.isLoggedIn && to.path === '/login') {
      next('/dashboard')
    } else {
      next()
    }
    return
  }

  // 未登录 → 跳转登录页
  if (!userStore.isLoggedIn) {
    next(`/login?redirect=${encodeURIComponent(to.fullPath)}`)
    return
  }

  // 已登录但没有管理员信息，先拉取
  if (!userStore.adminInfo) {
    try {
      await userStore.fetchAdminInfo()
    } catch {
      // token 失效
      await userStore.doLogout()
      next(`/login?redirect=${encodeURIComponent(to.fullPath)}`)
      return
    }
  }

  next()
})

router.afterEach((to) => {
  NProgress.done()
  // 同步标签页
  if (to.meta?.title && !to.meta?.hidden) {
    const tabsStore = useTabsStore()
    tabsStore.addTab({
      path: to.path,
      title: to.meta.title as string,
      name: to.name as string
    })
  }
  // 修改页面 title
  document.title = to.meta?.title
    ? `${to.meta.title} — ${import.meta.env.VITE_APP_TITLE}`
    : import.meta.env.VITE_APP_TITLE
})
