# 中小学生青少年学习社区 — 后台管理系统

> 技术栈：Vue 3 + TypeScript + Vite 5 + Element Plus + Pinia + Vue Router 4

## 快速启动

```bash
# 进入项目目录
cd admin

# 安装依赖
npm install

# 启动开发服务器（默认 http://localhost:3000）
npm run dev

# 构建生产产物
npm run build
```

## 目录结构

```
admin/
├── index.html
├── vite.config.ts           # Vite 配置（含代理）
├── tsconfig.json
├── .env.development         # 开发环境变量
├── .env.production          # 生产环境变量
└── src/
    ├── main.ts              # 应用入口
    ├── App.vue
    ├── api/                 # 接口模块
    │   ├── auth.ts          # 登录/鉴权
    │   ├── user.ts          # 用户管理
    │   ├── tutor.ts         # 陪伴师管理
    │   ├── community.ts     # 社区管理
    │   ├── order.ts         # 订单/佣金/提现
    │   ├── risk.ts          # 风控管理
    │   └── system.ts        # 系统配置
    ├── assets/styles/
    │   └── index.css        # 全局样式
    ├── hooks/               # 组合式 Hook
    │   ├── useList.ts       # 通用列表 Hook
    │   └── useFormDialog.ts # 通用表单弹窗 Hook
    ├── layout/              # 整体布局
    │   ├── index.vue        # 主布局
    │   └── components/
    │       ├── AppSidebar.vue   # 侧边栏（菜单）
    │       ├── AppHeader.vue    # 顶部栏（面包屑+用户）
    │       └── TagsView.vue     # 多标签页
    ├── router/
    │   ├── index.ts         # 路由定义（所有业务模块）
    │   └── permission.ts    # 路由守卫 / 权限拦截
    ├── store/               # Pinia 状态管理
    │   ├── index.ts
    │   ├── user.ts          # 用户/登录状态
    │   ├── app.ts           # 应用全局状态（侧边栏折叠等）
    │   └── tabs.ts          # 多标签页状态
    ├── utils/
    │   ├── request.ts       # Axios 封装（请求/响应拦截）
    │   ├── storage.ts       # localStorage 工具
    │   ├── format.ts        # 格式化工具（时间/金额/文件大小等）
    │   └── validate.ts      # 校验工具（手机号/邮箱等）
    └── views/
        ├── login/index.vue          # 登录页
        ├── dashboard/
        │   ├── index.vue            # 工作台
        │   └── 404.vue             # 404 页
        ├── user/
        │   ├── student.vue          # 学生列表
        │   └── parent.vue           # 家长列表
        ├── guardian/index.vue       # 家长监护绑定
        ├── tutor/
        │   ├── list.vue             # 陪伴师列表
        │   └── audit.vue            # 资质审核
        ├── community/
        │   ├── post.vue             # 社区动态
        │   └── topic.vue            # 话题管理
        ├── study/
        │   ├── checkin.vue          # 学习打卡
        │   ├── plan.vue             # 学习计划
        │   └── room.vue             # 自习室管理
        ├── order/list.vue           # 订单列表
        ├── finance/
        │   ├── commission.vue       # 佣金流水
        │   └── withdraw.vue         # 提现审核
        ├── risk/
        │   ├── record.vue           # 违规记录
        │   └── ban.vue              # 封禁列表
        └── system/
            ├── config.vue           # 系统配置
            └── admin.vue            # 管理员管理
```

## 核心功能

| 模块 | 功能说明 |
|------|---------|
| 登录 | 账号密码登录，JWT token 存储，自动跳转 |
| 权限拦截 | 路由守卫，未登录自动重定向，token 失效自动登出 |
| 布局 | 固定侧边栏 + 顶部面包屑 + 多标签页（右键关闭/关闭全部） |
| 用户管理 | 学生/家长列表，禁用/启用 |
| 陪伴师管理 | 列表、上下线、资质审核（通过/拒绝） |
| 社区管理 | 帖子审核/删除，话题 CRUD |
| 学习管理 | 打卡记录、学习计划、自习室配置 |
| 订单管理 | 订单列表，状态筛选 |
| 财务管理 | 佣金流水，提现审核 |
| 风控管理 | 违规记录处理，用户封禁/解封 |
| 系统配置 | 分组配置，单项/批量保存 |
| 管理员管理 | 管理员 CRUD，禁用/启用 |

## 环境变量

| 变量 | 说明 |
|------|------|
| `VITE_APP_TITLE` | 系统名称 |
| `VITE_API_BASE_URL` | 后端 API 地址 |
| `VITE_TOKEN_KEY` | localStorage token 键名 |
