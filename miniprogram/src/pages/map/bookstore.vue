<template>
  <view class="scene-page">
    <view class="scene-header">
      <view class="back-btn" @tap="goBack">
        <u-icon name="arrow-left" size="20" color="#fff" />
        <text>返回地图</text>
      </view>
      <text class="scene-title">📖 书店</text>
    </view>

    <view class="scene-content">
      <view class="scene-banner">
        <text class="banner-icon">📖</text>
        <text class="banner-title">欢迎来到书店</text>
        <text class="banner-desc">精选好书，发现阅读乐趣</text>
      </view>

      <view class="feature-grid">
        <view class="feature-card" @tap="go全部书目">
          <text class="feature-icon">🔍</text>
          <text class="feature-name">全部书目</text>
          <text class="feature-desc">海量书籍选购</text>
        </view>
        <view class="feature-card" @tap="go每周好书">
          <text class="feature-icon">🌟</text>
          <text class="feature-name">每周好书</text>
          <text class="feature-desc">编辑推荐</text>
        </view>
        <view class="feature-card" @tap="go教辅材料">
          <text class="feature-icon">🎒</text>
          <text class="feature-name">教辅材料</text>
          <text class="feature-desc">课内教辅</text>
        </view>
        <view class="feature-card" @tap="go课外读物">
          <text class="feature-icon">💡</text>
          <text class="feature-name">课外读物</text>
          <text class="feature-desc">拓展知识面</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
function goBack() { uni.navigateBack() }
function go全部书目() { uni.showToast({ title: '全部书目（开发中）', icon: 'none' }) }
function go每周好书() { uni.showToast({ title: '每周好书（开发中）', icon: 'none' }) }
function go教辅材料() { uni.showToast({ title: '教辅材料（开发中）', icon: 'none' }) }
function go课外读物() { uni.showToast({ title: '课外读物（开发中）', icon: 'none' }) }
</script>

<style lang="scss" scoped>
@import './scene.scss';
</style>
