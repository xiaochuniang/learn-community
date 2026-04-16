import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

/** 不需要鉴权的白名单路由 */
export const whiteList = ['/login']

/** 布局组件 */
const Layout = () => import('@/layout/index.vue')

export const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/login/index.vue'),
    meta: { title: '登录', hidden: true }
  },
  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/index.vue'),
        meta: { title: '工作台', icon: 'Odometer', affix: true }
      }
    ]
  },
  // ==================== 用户管理 ====================
  {
    path: '/user',
    component: Layout,
    redirect: '/user/student',
    meta: { title: '用户管理', icon: 'User' },
    children: [
      {
        path: 'student',
        name: 'UserStudent',
        component: () => import('@/views/user/student.vue'),
        meta: { title: '学生列表', icon: 'UserFilled' }
      },
      {
        path: 'parent',
        name: 'UserParent',
        component: () => import('@/views/user/parent.vue'),
        meta: { title: '家长列表', icon: 'Avatar' }
      },
      {
        path: 'guardian',
        name: 'UserGuardian',
        component: () => import('@/views/guardian/index.vue'),
        meta: { title: '监护绑定', icon: 'Link' }
      }
    ]
  },
  // ==================== 陪伴师管理 ====================
  {
    path: '/tutor',
    component: Layout,
    redirect: '/tutor/list',
    meta: { title: '陪伴师管理', icon: 'Notebook' },
    children: [
      {
        path: 'list',
        name: 'TutorList',
        component: () => import('@/views/tutor/list.vue'),
        meta: { title: '陪伴师列表', icon: 'Memo' }
      },
      {
        path: 'audit',
        name: 'TutorAudit',
        component: () => import('@/views/tutor/audit.vue'),
        meta: { title: '资质审核', icon: 'DocumentChecked' }
      }
    ]
  },
  // ==================== 社区管理 ====================
  {
    path: '/community',
    component: Layout,
    redirect: '/community/post',
    meta: { title: '社区管理', icon: 'ChatDotRound' },
    children: [
      {
        path: 'post',
        name: 'CommunityPost',
        component: () => import('@/views/community/post.vue'),
        meta: { title: '社区动态', icon: 'Document' }
      },
      {
        path: 'topic',
        name: 'CommunityTopic',
        component: () => import('@/views/community/topic.vue'),
        meta: { title: '话题管理', icon: 'Collection' }
      }
    ]
  },
  // ==================== 学习管理 ====================
  {
    path: '/study',
    component: Layout,
    redirect: '/study/checkin',
    meta: { title: '学习管理', icon: 'Reading' },
    children: [
      {
        path: 'checkin',
        name: 'StudyCheckIn',
        component: () => import('@/views/study/checkin.vue'),
        meta: { title: '学习打卡', icon: 'Finished' }
      },
      {
        path: 'plan',
        name: 'StudyPlan',
        component: () => import('@/views/study/plan.vue'),
        meta: { title: '学习计划', icon: 'Calendar' }
      },
      {
        path: 'room',
        name: 'StudyRoom',
        component: () => import('@/views/study/room.vue'),
        meta: { title: '自习室管理', icon: 'Monitor' }
      }
    ]
  },
  // ==================== 订单管理 ====================
  {
    path: '/order',
    component: Layout,
    redirect: '/order/list',
    meta: { title: '订单管理', icon: 'ShoppingCart' },
    children: [
      {
        path: 'list',
        name: 'OrderList',
        component: () => import('@/views/order/list.vue'),
        meta: { title: '订单列表', icon: 'List' }
      }
    ]
  },
  // ==================== 财务管理 ====================
  {
    path: '/finance',
    component: Layout,
    redirect: '/finance/commission',
    meta: { title: '财务管理', icon: 'CreditCard' },
    children: [
      {
        path: 'commission',
        name: 'FinanceCommission',
        component: () => import('@/views/finance/commission.vue'),
        meta: { title: '佣金流水', icon: 'Money' }
      },
      {
        path: 'withdraw',
        name: 'FinanceWithdraw',
        component: () => import('@/views/finance/withdraw.vue'),
        meta: { title: '提现审核', icon: 'Wallet' }
      }
    ]
  },
  // ==================== 风控管理 ====================
  {
    path: '/risk',
    component: Layout,
    redirect: '/risk/record',
    meta: { title: '风控管理', icon: 'Warning' },
    children: [
      {
        path: 'record',
        name: 'RiskRecord',
        component: () => import('@/views/risk/record.vue'),
        meta: { title: '违规记录', icon: 'WarnTriangleFilled' }
      },
      {
        path: 'ban',
        name: 'RiskBan',
        component: () => import('@/views/risk/ban.vue'),
        meta: { title: '封禁列表', icon: 'Lock' }
      }
    ]
  },
  // ==================== 虚拟地图管理 ====================
  {
    path: '/map',
    component: Layout,
    redirect: '/map/points',
    meta: { title: '虚拟地图', icon: 'MapLocation' },
    children: [
      {
        path: 'points',
        name: 'MapPoints',
        component: () => import('@/views/map/points.vue'),
        meta: { title: '点位管理', icon: 'Location' }
      },
      {
        path: 'character',
        name: 'MapCharacter',
        component: () => import('@/views/map/character.vue'),
        meta: { title: '人物配置', icon: 'Avatar' }
      }
    ]
  },
  // ==================== 系统管理 ====================
  {
    path: '/system',
    component: Layout,
    redirect: '/system/config',
    meta: { title: '系统管理', icon: 'Setting' },
    children: [
      {
        path: 'config',
        name: 'SystemConfig',
        component: () => import('@/views/system/config.vue'),
        meta: { title: '系统配置', icon: 'Tools' }
      },
      {
        path: 'admin',
        name: 'SystemAdmin',
        component: () => import('@/views/system/admin.vue'),
        meta: { title: '管理员管理', icon: 'UserFilled' }
      }
    ]
  },
  // 404
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/dashboard/404.vue'),
    meta: { title: '页面不存在', hidden: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 })
})

export default router
