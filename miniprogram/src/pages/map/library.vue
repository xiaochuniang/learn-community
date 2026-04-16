<template>
  <view class="scene-page">
    <view class="scene-header">
      <view class="back-btn" @tap="goBack">
        <u-icon name="arrow-left" size="20" color="#fff" />
        <text>返回地图</text>
      </view>
      <text class="scene-title">📚 图书馆</text>
    </view>

    <view class="scene-content">
      <view class="scene-banner">
        <text class="banner-icon">📚</text>
        <text class="banner-title">欢迎来到图书馆</text>
        <text class="banner-desc">这里有海量书籍，随时借阅，沉浸阅读</text>
      </view>

      <view class="feature-grid">
        <view class="feature-card" @tap="goBorrow">
          <text class="feature-icon">📖</text>
          <text class="feature-name">借阅书目</text>
          <text class="feature-desc">浏览并借阅图书</text>
        </view>
        <view class="feature-card" @tap="goMyBooks">
          <text class="feature-icon">🗂️</text>
          <text class="feature-name">我的借阅</text>
          <text class="feature-desc">查看在借书籍</text>
        </view>
        <view class="feature-card" @tap="goRecommend">
          <text class="feature-icon">⭐</text>
          <text class="feature-name">好书推荐</text>
          <text class="feature-desc">编辑精选书单</text>
        </view>
        <view class="feature-card" @tap="goReading">
          <text class="feature-icon">🎧</text>
          <text class="feature-name">有声图书</text>
          <text class="feature-desc">边听边学</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
function goBack() { uni.navigateBack() }
function goBorrow()    { uni.showToast({ title: '图书借阅（开发中）', icon: 'none' }) }
function goMyBooks()   { uni.showToast({ title: '我的借阅（开发中）', icon: 'none' }) }
function goRecommend() { uni.showToast({ title: '好书推荐（开发中）', icon: 'none' }) }
function goReading()   { uni.showToast({ title: '有声图书（开发中）', icon: 'none' }) }
</script>

<style lang="scss" scoped>
@import './scene.scss';
</style>
