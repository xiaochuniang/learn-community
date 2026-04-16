<template>
  <view class="scene-page">
    <view class="scene-header">
      <view class="back-btn" @tap="goBack">
        <u-icon name="arrow-left" size="20" color="#fff" />
        <text>返回地图</text>
      </view>
      <text class="scene-title">🖊️ 文具店</text>
    </view>

    <view class="scene-content">
      <view class="scene-banner">
        <text class="banner-icon">🖊️</text>
        <text class="banner-title">欢迎来到文具店</text>
        <text class="banner-desc">学习文具一站齐全</text>
      </view>

      <view class="feature-grid">
        <view class="feature-card" @tap="go文具选购">
          <text class="feature-icon">📝</text>
          <text class="feature-name">文具选购</text>
          <text class="feature-desc">铅笔本子样样全</text>
        </view>
        <view class="feature-card" @tap="go书包背包">
          <text class="feature-icon">🎒</text>
          <text class="feature-name">书包背包</text>
          <text class="feature-desc">轻便好用</text>
        </view>
        <view class="feature-card" @tap="go理科工具">
          <text class="feature-icon">🔬</text>
          <text class="feature-name">理科工具</text>
          <text class="feature-desc">圆规量角器</text>
        </view>
        <view class="feature-card" @tap="go美术用品">
          <text class="feature-icon">🎨</text>
          <text class="feature-name">美术用品</text>
          <text class="feature-desc">颜料画笔画板</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
function goBack() { uni.navigateBack() }
function go文具选购() { uni.showToast({ title: '文具选购（开发中）', icon: 'none' }) }
function go书包背包() { uni.showToast({ title: '书包背包（开发中）', icon: 'none' }) }
function go理科工具() { uni.showToast({ title: '理科工具（开发中）', icon: 'none' }) }
function go美术用品() { uni.showToast({ title: '美术用品（开发中）', icon: 'none' }) }
</script>

<style lang="scss" scoped>
@import './scene.scss';
</style>
