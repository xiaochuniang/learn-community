<template>
  <div class="login-page">
    <div class="login-box">
      <div class="login-header">
        <span class="login-logo">📚</span>
        <h2 class="login-title">学习社区后台管理</h2>
        <p class="login-subtitle">Youth Learning Community Admin</p>
      </div>

      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        class="login-form"
        @keyup.enter="handleLogin"
      >
        <el-form-item prop="username">
          <el-input
            v-model="form.username"
            placeholder="请输入账号"
            size="large"
            :prefix-icon="User"
            clearable
          />
        </el-form-item>

        <el-form-item prop="password">
          <el-input
            v-model="form.password"
            placeholder="请输入密码"
            size="large"
            type="password"
            :prefix-icon="Lock"
            show-password
            clearable
          />
        </el-form-item>

        <el-button
          type="primary"
          size="large"
          class="login-btn"
          :loading="loading"
          @click="handleLogin"
        >
          登 录
        </el-button>
      </el-form>
    </div>

    <!-- 背景装饰 -->
    <div class="bg-circle bg-circle-1"></div>
    <div class="bg-circle bg-circle-2"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { User, Lock } from '@element-plus/icons-vue'
import { useUserStore } from '@/store/user'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const formRef = ref<FormInstance>()
const loading = ref(false)

const form = reactive({
  username: 'admin',
  password: ''
})

const rules: FormRules = {
  username: [{ required: true, message: '请输入账号', trigger: 'blur' }],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, message: '密码至少6位', trigger: 'blur' }
  ]
}

async function handleLogin() {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    loading.value = true
    try {
      await userStore.doLogin({ username: form.username, password: form.password })
      ElMessage.success('登录成功')
      const redirect = (route.query.redirect as string) || '/dashboard'
      router.push(redirect)
    } catch {
      // 错误已在 request.ts 中处理
    } finally {
      loading.value = false
    }
  })
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #1890ff 0%, #096dd9 50%, #0050b3 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.login-box {
  width: 420px;
  background: #fff;
  border-radius: 16px;
  padding: 48px 40px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  position: relative;
  z-index: 1;
}

.login-header {
  text-align: center;
  margin-bottom: 36px;
}

.login-logo {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
}

.login-title {
  font-size: 22px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 6px;
}

.login-subtitle {
  font-size: 13px;
  color: #909399;
}

.login-form :deep(.el-form-item) {
  margin-bottom: 20px;
}

.login-btn {
  width: 100%;
  height: 44px;
  font-size: 16px;
  margin-top: 8px;
  border-radius: 8px;
}

/* 背景装饰圆 */
.bg-circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
}

.bg-circle-1 {
  width: 400px;
  height: 400px;
  top: -100px;
  right: -100px;
}

.bg-circle-2 {
  width: 300px;
  height: 300px;
  bottom: -80px;
  left: -80px;
}
</style>
