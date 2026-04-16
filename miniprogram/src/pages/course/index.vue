<template>
  <view class="course-page">
    <!-- 搜索框 -->
    <view class="search-area">
      <u-search
        v-model="keyword"
        placeholder="搜索课程名称"
        :show-action="false"
        bg-color="#f0f4ff"
        @change="onSearch"
        @clear="onSearch"
      />
    </view>

    <!-- 分类 tab -->
    <scroll-view scroll-x class="category-scroll">
      <view class="category-list">
        <view
          v-for="cat in categories"
          :key="cat.id"
          class="category-item"
          :class="{ active: activeCat === cat.id }"
          @tap="changeCat(cat.id)"
        >{{ cat.name }}</view>
      </view>
    </scroll-view>

    <!-- 排序栏 -->
    <view class="sort-bar">
      <view
        v-for="s in sortOptions"
        :key="s.value"
        class="sort-item"
        :class="{ active: sort === s.value }"
        @tap="changeSort(s.value)"
      >
        {{ s.label }}
        <u-icon v-if="s.value !== 'new'" :name="sort === s.value ? 'arrow-up' : 'arrow-down'" size="12" />
      </view>
      <view class="sort-item" :class="{ active: onlyFree }" @tap="onlyFree = !onlyFree; loadCourses()">免费</view>
    </view>

    <!-- 课程列表 -->
    <view class="course-list" v-if="!loading">
      <view
        v-for="course in courses"
        :key="course.id"
        class="course-item"
        @tap="goCourseDetail(course.id)"
      >
        <image :src="course.coverUrl" class="course-cover" mode="aspectFill" />
        <view class="course-body">
          <text class="course-title">{{ course.title }}</text>
          <text class="course-teacher">{{ course.teacherName }}</text>
          <view class="course-meta">
            <text class="course-category">{{ course.categoryName }}</text>
            <u-rate :value="Number(course.rating)" :size="14" :read-only="true" />
          </view>
          <view class="course-bottom">
            <text class="course-price" :class="{ free: course.isFree }">
              {{ course.isFree ? '免费' : `¥ ${course.price}` }}
            </text>
            <text class="course-students">{{ course.studentCount }}人学习</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 骨架屏 loading -->
    <view v-else class="skeleton-list">
      <view v-for="i in 4" :key="i" class="skeleton-item">
        <view class="sk-cover" />
        <view class="sk-body">
          <view class="sk-title" />
          <view class="sk-sub" />
        </view>
      </view>
    </view>

    <!-- 空状态 -->
    <u-empty v-if="!loading && courses.length === 0" text="暂无课程" mode="list" />

    <!-- 加载更多 -->
    <u-loadmore
      :status="loadMoreStatus"
      @loadmore="onLoadMore"
    />

    <!-- 底部占位 -->
    <view style="height: 32rpx" />
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import { getCourseList, type CourseItem } from '@/api/home'

const keyword = ref('')
const activeCat = ref(0)
const sort = ref('new')
const onlyFree = ref(false)
const loading = ref(false)
const courses = ref<CourseItem[]>([])
const page = ref(1)
const total = ref(0)
const PAGE_SIZE = 10

type LoadMoreStatus = 'loadmore' | 'loading' | 'nomore'
const loadMoreStatus = ref<LoadMoreStatus>('loadmore')

// 分类（实际项目中从接口拉取）
const categories = [
  { id: 0, name: '全部' },
  { id: 1, name: '数学' },
  { id: 2, name: '语文' },
  { id: 3, name: '英语' },
  { id: 4, name: '物理' },
  { id: 5, name: '化学' },
  { id: 6, name: '编程' }
]

const sortOptions = [
  { label: '最新', value: 'new' },
  { label: '最热', value: 'hot' },
  { label: '价格', value: 'price' }
]

async function loadCourses(append = false) {
  if (!append) {
    page.value = 1
    loading.value = true
  } else {
    loadMoreStatus.value = 'loading'
  }
  try {
    const res = await getCourseList({
      page: page.value,
      pageSize: PAGE_SIZE,
      keyword: keyword.value,
      categoryId: activeCat.value || undefined,
      isFree: onlyFree.value || undefined,
      sort: sort.value
    })
    total.value = res.data.total
    if (append) {
      courses.value = [...courses.value, ...res.data.list]
    } else {
      courses.value = res.data.list
    }
    loadMoreStatus.value = courses.value.length >= total.value ? 'nomore' : 'loadmore'
  } finally {
    loading.value = false
  }
}

onLoad(() => loadCourses())

onPullDownRefresh(async () => {
  await loadCourses()
  uni.stopPullDownRefresh()
})

function onSearch() {
  loadCourses()
}

function changeCat(id: number) {
  activeCat.value = id
  loadCourses()
}

function changeSort(val: string) {
  sort.value = val
  loadCourses()
}

function onLoadMore() {
  if (loadMoreStatus.value === 'nomore') return
  page.value++
  loadCourses(true)
}

function goCourseDetail(id: number) {
  uni.navigateTo({ url: `/pages/course-detail/index?id=${id}` })
}
</script>

<style lang="scss" scoped>
.course-page {
  background: #f4f6f8;
  min-height: 100vh;
}

.search-area {
  padding: 20rpx 24rpx;
  background: #fff;
}

.category-scroll {
  background: #fff;
  border-bottom: 1rpx solid #f0f0f0;
}

.category-list {
  display: flex;
  padding: 12rpx 16rpx;
  gap: 8rpx;
  white-space: nowrap;
}

.category-item {
  padding: 10rpx 28rpx;
  border-radius: 40rpx;
  font-size: 26rpx;
  color: #606266;
  flex-shrink: 0;
  background: #f4f6f8;
  transition: all 0.2s;

  &.active {
    background: $primary-color;
    color: #fff;
    font-weight: 600;
  }
}

.sort-bar {
  display: flex;
  padding: 18rpx 32rpx;
  gap: 32rpx;
  background: #fff;
  margin-bottom: 16rpx;
  font-size: 26rpx;
}

.sort-item {
  color: #909399;
  display: flex;
  align-items: center;
  gap: 4rpx;

  &.active {
    color: $primary-color;
    font-weight: 600;
  }
}

.course-list { padding: 0 24rpx; }

.course-item {
  display: flex;
  gap: 20rpx;
  background: #fff;
  border-radius: 16rpx;
  margin-bottom: 20rpx;
  padding: 20rpx;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.04);
}

.course-cover {
  width: 180rpx;
  height: 130rpx;
  border-radius: 12rpx;
  flex-shrink: 0;
}

.course-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.course-title {
  font-size: 28rpx;
  font-weight: 600;
  color: #1a1a2e;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-teacher { font-size: 22rpx; color: #8a8fa8; }

.course-meta {
  display: flex;
  align-items: center;
  gap: 12rpx;
}

.course-category {
  font-size: 20rpx;
  background: #eff6ff;
  color: $primary-color;
  padding: 2rpx 12rpx;
  border-radius: 20rpx;
}

.course-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}

.course-price {
  font-size: 30rpx;
  font-weight: 700;
  color: #ef4444;
  &.free { color: #22c55e; }
}

.course-students { font-size: 20rpx; color: #aaa; }

/* 骨架屏 */
.skeleton-list { padding: 0 24rpx; }

.skeleton-item {
  display: flex;
  gap: 20rpx;
  background: #fff;
  border-radius: 16rpx;
  margin-bottom: 20rpx;
  padding: 20rpx;
}

.sk-cover {
  width: 180rpx;
  height: 130rpx;
  border-radius: 12rpx;
  background: #f0f0f0;
  flex-shrink: 0;
  animation: pulse 1.4s ease-in-out infinite;
}

.sk-body { flex: 1; display: flex; flex-direction: column; gap: 16rpx; padding-top: 8rpx; }

.sk-title {
  height: 32rpx;
  background: #f0f0f0;
  border-radius: 6rpx;
  width: 90%;
  animation: pulse 1.4s ease-in-out infinite;
}

.sk-sub {
  height: 24rpx;
  background: #f0f0f0;
  border-radius: 6rpx;
  width: 50%;
  animation: pulse 1.4s ease-in-out 0.2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
