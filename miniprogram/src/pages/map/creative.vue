<template>
  <view class="scene-page">
    <view class="scene-header">
      <view class="back-btn" @tap="goBack">
        <u-icon name="arrow-left" size="20" color="#fff" />
        <text>返回地图</text>
      </view>
      <text class="scene-title">🎨 文创店</text>
    </view>

    <view class="scene-content">
      <view class="scene-banner">
        <text class="banner-icon">🎨</text>
        <text class="banner-title">欢迎来到文创店</text>
        <text class="banner-desc">激发你的创意灵感</text>
      </view>

      <view class="feature-grid">
        <view class="feature-card" @tap="go文创选品">
          <text class="feature-icon">🖌️</text>
          <text class="feature-name">文创选品</text>
          <text class="feature-desc">精选创意好物</text>
        </view>
        <view class="feature-card" @tap="go手工工坊">
          <text class="feature-icon">✂️</text>
          <text class="feature-name">手工工坊</text>
          <text class="feature-desc">动手制作乐趣</text>
        </view>
        <view class="feature-card" @tap="go定制周边">
          <text class="feature-icon">🎁</text>
          <text class="feature-name">定制周边</text>
          <text class="feature-desc">专属纪念品</text>
        </view>
        <view class="feature-card" @tap="go创意大赛">
          <text class="feature-icon">🏆</text>
          <text class="feature-name">创意大赛</text>
          <text class="feature-desc">展示你的才华</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
function goBack() { uni.navigateBack() }
function go文创选品() { uni.showToast({ title: '文创选品（开发中）', icon: 'none' }) }
function go手工工坊() { uni.showToast({ title: '手工工坊（开发中）', icon: 'none' }) }
function go定制周边() { uni.showToast({ title: '定制周边（开发中）', icon: 'none' }) }
function go创意大赛() { uni.showToast({ title: '创意大赛（开发中）', icon: 'none' }) }
</script>

<style lang="scss" scoped>
@import './scene.scss';
</style>
