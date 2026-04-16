import { createSSRApp } from 'vue'
import { createPinia } from 'pinia'
import uviewPlus from 'uview-plus'
import App from './App.vue'

export function createApp() {
  const app = createSSRApp(App)

  // Pinia 状态管理
  app.use(createPinia())

  // uView Plus 组件库
  app.use(uviewPlus)

  return { app }
}
