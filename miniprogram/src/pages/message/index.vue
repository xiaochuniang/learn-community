<template>
  <view class="message-page">
    <!-- Tab 切换 -->
    <u-tabs :list="tabs" :current="activeTab" @click="onTabClick" lineColor="#3b82f6" />

    <!-- 消息列表 -->
    <view class="msg-list" v-if="messages.length > 0">
      <view
        v-for="msg in messages"
        :key="msg.id"
        class="msg-item"
        @tap="goDetail(msg)"
      >
        <view class="msg-avatar">
          <image :src="msg.avatarUrl || '/static/images/system-icon.png'" mode="aspectFill" class="avatar-img" />
          <view v-if="!msg.isRead" class="unread-dot" />
        </view>
        <view class="msg-body">
          <view class="msg-header">
            <text class="msg-sender">{{ msg.senderName }}</text>
            <text class="msg-time">{{ timeAgo(msg.createTime) }}</text>
          </view>
          <text class="msg-content">{{ msg.content }}</text>
        </view>
      </view>
    </view>

    <u-empty v-else-if="!loading" text="暂无消息" mode="message" />

    <u-loadmore :status="loadMoreStatus" @loadmore="onLoadMore" />

    <view style="height: 32rpx" />
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad, onPullDownRefresh } from '@dcloudio/uni-app'
import request from '@/utils/request'
import { timeAgo } from '@/utils/format'

interface MessageItem {
  id: number
  type: number        // 1系统通知 2课程消息 3陪伴师消息
  senderName: string
  avatarUrl: string
  content: string
  isRead: boolean
  linkType: string
  linkId: number
  createTime: string
}

const tabs = [
  { name: '全部' },
  { name: '系统通知' },
  { name: '课程消息' },
  { name: '陪伴师' }
]

const activeTab = ref(0)
const loading = ref(false)
const messages = ref<MessageItem[]>([])
const page = ref(1)
const total = ref(0)
type LoadMoreStatus = 'loadmore' | 'loading' | 'nomore'
const loadMoreStatus = ref<LoadMoreStatus>('loadmore')

async function loadMessages(append = false) {
  if (!append) { page.value = 1; loading.value = true }
  else loadMoreStatus.value = 'loading'
  try {
    const res = await request.get<{ list: MessageItem[]; total: number }>('/message/list', {
      page: page.value,
      pageSize: 15,
      type: activeTab.value === 0 ? undefined : activeTab.value
    })
    total.value = res.data.total
    messages.value = append ? [...messages.value, ...res.data.list] : res.data.list
    loadMoreStatus.value = messages.value.length >= total.value ? 'nomore' : 'loadmore'
  } finally {
    loading.value = false
  }
}

onLoad(() => loadMessages())

onPullDownRefresh(async () => {
  await loadMessages()
  uni.stopPullDownRefresh()
})

function onTabClick(index: number) {
  activeTab.value = index
  loadMessages()
}

function onLoadMore() {
  if (loadMoreStatus.value === 'nomore') return
  page.value++
  loadMessages(true)
}

function goDetail(msg: MessageItem) {
  // 标记已读
  if (!msg.isRead) {
    request.put(`/message/${msg.id}/read`)
    msg.isRead = true
  }
  if (msg.linkType === 'course') uni.navigateTo({ url: `/pages/course-detail/index?id=${msg.linkId}` })
  else if (msg.linkType === 'tutor') uni.navigateTo({ url: `/pages/tutor/detail?id=${msg.linkId}` })
  else if (msg.linkType === 'order') uni.navigateTo({ url: `/pages/order/detail?id=${msg.linkId}` })
}
</script>

<style lang="scss" scoped>
.message-page {
  background: #f4f6f8;
  min-height: 100vh;
}

.msg-list { padding: 16rpx 24rpx; }

.msg-item {
  display: flex;
  gap: 20rpx;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 16rpx;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.04);
}

.msg-avatar {
  position: relative;
  flex-shrink: 0;
}

.avatar-img {
  width: 80rpx;
  height: 80rpx;
  border-radius: 50%;
}

.unread-dot {
  position: absolute;
  top: 0;
  right: 0;
  width: 18rpx;
  height: 18rpx;
  background: #ef4444;
  border-radius: 50%;
  border: 2rpx solid #fff;
}

.msg-body { flex: 1; min-width: 0; }

.msg-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8rpx;
}

.msg-sender {
  font-size: 28rpx;
  font-weight: 600;
  color: #1a1a2e;
}

.msg-time { font-size: 22rpx; color: #aaa; }

.msg-content {
  font-size: 26rpx;
  color: #606266;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
