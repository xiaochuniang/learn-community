<template>
  <view class="scene-page">
    <view class="scene-header">
      <view class="back-btn" @tap="goBack">
        <u-icon name="arrow-left" size="20" color="#fff" />
        <text>返回地图</text>
      </view>
      <text class="scene-title">🎭 社团</text>
    </view>

    <view class="scene-content">
      <view class="scene-banner">
        <text class="banner-icon">🎭</text>
        <text class="banner-title">欢迎来到社团</text>
        <text class="banner-desc">结交志同道合的朋友</text>
      </view>

      <view class="feature-grid">
        <view class="feature-card" @tap="go科技社团">
          <text class="feature-icon">🔭</text>
          <text class="feature-name">科技社团</text>
          <text class="feature-desc">探索科学奥秘</text>
        </view>
        <view class="feature-card" @tap="go音乐社团">
          <text class="feature-icon">🎵</text>
          <text class="feature-name">音乐社团</text>
          <text class="feature-desc">演奏你的旋律</text>
        </view>
        <view class="feature-card" @tap="go环保社团">
          <text class="feature-icon">🌱</text>
          <text class="feature-name">环保社团</text>
          <text class="feature-desc">保护我们的地球</text>
        </view>
        <view class="feature-card" @tap="go摄影社团">
          <text class="feature-icon">📸</text>
          <text class="feature-name">摄影社团</text>
          <text class="feature-desc">用镜头记录生活</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
function goBack() { uni.navigateBack() }
function go科技社团() { uni.showToast({ title: '科技社团（开发中）', icon: 'none' }) }
function go音乐社团() { uni.showToast({ title: '音乐社团（开发中）', icon: 'none' }) }
function go环保社团() { uni.showToast({ title: '环保社团（开发中）', icon: 'none' }) }
function go摄影社团() { uni.showToast({ title: '摄影社团（开发中）', icon: 'none' }) }
</script>

<style lang="scss" scoped>
@import './scene.scss';
</style>
