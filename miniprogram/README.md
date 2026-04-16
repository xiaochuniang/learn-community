# 学习社区小程序 (learn-community-miniprogram)

> Vue3 + TypeScript + Vite + uni-app + uView Plus  
> 面向微信小程序平台

---

## 目录结构

```
miniprogram/
├── src/
│   ├── App.vue               # 应用根组件（注册全局样式、初始化登录）
│   ├── main.ts               # 入口文件（注册 Pinia、uView Plus）
│   ├── manifest.json         # uni-app 应用配置（AppID 等）
│   ├── pages.json            # 页面路由 + tabBar + 全局样式
│   │
│   ├── pages/
│   │   ├── home/index.vue        # 首页（Banner + 快捷入口 + 推荐课程/陪伴师）
│   │   ├── course/index.vue      # 课程列表（搜索 + 分类 + 排序 + 下拉刷新 + 上拉加载）
│   │   ├── message/index.vue     # 消息中心（Tab 切换 + 已读/未读 + 跳转）
│   │   ├── profile/index.vue     # 我的（用户信息 + 订单入口 + 功能菜单 + 退出）
│   │   ├── login/index.vue       # 登录页（微信一键登录 + 游客模式）
│   │   └── auth/index.vue        # 授权中转页（鉴权守卫）
│   │
│   ├── stores/
│   │   └── user.ts               # Pinia 用户 Store（登录/登出/拉取用户信息）
│   │
│   ├── api/
│   │   ├── auth.ts               # 认证接口（wx登录、绑定手机、刷新token）
│   │   ├── user.ts               # 用户接口（用户信息、更新资料、积分明细）
│   │   └── home.ts               # 首页/课程接口（Banner、推荐、课程列表）
│   │
│   ├── utils/
│   │   ├── request.ts            # uni.request 封装（统一拦截、Token 注入、错误处理）
│   │   ├── storage.ts            # 本地存储工具（Token 管理 + 通用 get/set/remove）
│   │   ├── format.ts             # 格式化工具（时间、金额、脱敏、截断等）
│   │   └── validate.ts           # 校验工具（手机号、身份证、URL、密码强度等）
│   │
│   └── styles/
│       ├── variables.scss        # SCSS 变量（颜色、字号、间距、阴影等）
│       └── index.scss            # 全局样式（重置、工具类、卡片、骨架屏动画）
│
├── .env.development          # 开发环境变量
├── .env.production           # 生产环境变量
├── package.json
├── tsconfig.json
└── vite.config.ts
```

---

## 快速开始

### 1. 安装依赖

```bash
cd miniprogram
npm install
```

### 2. 开发构建（输出至 dist/dev/mp-weixin）

```bash
npm run dev:mp-weixin
```

### 3. 用微信开发者工具打开

- 打开微信开发者工具
- 选择「小程序」→「导入项目」
- 目录选择 `dist/dev/mp-weixin`
- 填入你的 AppID（在 `src/manifest.json` 中替换 `wx__YOUR_APPID__`）

### 4. 生产构建

```bash
npm run build:mp-weixin
```

---

## 关键配置说明

| 文件 | 说明 |
|------|------|
| `src/manifest.json` | 替换 `wx__YOUR_APPID__` 为真实小程序 AppID |
| `.env.development` | 修改 `VITE_API_BASE_URL` 为后端接口地址 |
| `src/pages.json` | tabBar 图片路径需要替换为真实图标（`static/tabbar/*.png`） |

---

## tabBar 图标说明

请在 `src/static/tabbar/` 目录下放置以下 8 张图标（建议 81×81px）：

- `home.png` / `home-active.png`
- `course.png` / `course-active.png`
- `message.png` / `message-active.png`
- `profile.png` / `profile-active.png`

---

## 已实现功能

- ✅ 微信 `uni.login` 一键登录（code 换 token）
- ✅ 授权中转页（鉴权守卫，未登录自动跳转登录）
- ✅ Pinia 用户状态管理（自动从 Storage 恢复 Token）
- ✅ `uni.request` 完整封装（Token 自动注入、401 跳转登录、统一错误提示）
- ✅ 首页（Banner、快捷入口、推荐课程横向滚动、陪伴师 2 列网格）
- ✅ 课程列表（分类 Tab、排序、免费筛选、骨架屏、上拉加载更多、下拉刷新）
- ✅ 消息中心（Tab 分类、未读红点、点击跳转）
- ✅ 我的（用户信息、VIP 标识、统计数据、订单入口、功能菜单、退出登录）
- ✅ 全局 SCSS 变量 + 工具类
- ✅ easycom 自动注册 uView Plus 组件
