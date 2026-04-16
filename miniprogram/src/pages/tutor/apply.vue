<template>
  <view class="apply-page">
    <!-- ── 顶部步骤条 ─────────────────────────────────────── -->
    <view class="steps-header">
      <view
        v-for="(step, i) in steps"
        :key="i"
        class="step-item"
        :class="{ active: currentStep === i, done: currentStep > i }"
      >
        <view class="step-circle">
          <u-icon v-if="currentStep > i" name="checkmark" size="14" color="#fff" />
          <text v-else>{{ i + 1 }}</text>
        </view>
        <text class="step-label">{{ step }}</text>
        <view v-if="i < steps.length - 1" class="step-line" :class="{ done: currentStep > i }" />
      </view>
    </view>

    <scroll-view scroll-y class="form-scroll" :scroll-top="scrollTop">

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- STEP 0  身份选择                                    -->
      <!-- ═══════════════════════════════════════════════════ -->
      <view v-if="currentStep === 0" class="step-content">
        <view class="card">
          <text class="card-title">选择您的身份</text>
          <text class="card-desc">不同身份需要提供不同的资质证明，请如实选择</text>
          <view class="identity-grid">
            <view
              v-for="id in identityOptions"
              :key="id.value"
              class="identity-card"
              :class="{ selected: form.identityType === id.value }"
              @tap="selectIdentity(id.value)"
            >
              <view class="id-icon" :style="{ background: id.bg }">
                <u-icon :name="id.icon" size="32" :color="id.color" />
              </view>
              <text class="id-label">{{ id.label }}</text>
              <text class="id-desc">{{ id.desc }}</text>
              <view class="id-check" v-if="form.identityType === id.value">
                <u-icon name="checkmark-circle-fill" size="20" color="#3b82f6" />
              </view>
            </view>
          </view>
          <text v-if="errors.identityType" class="err-msg">{{ errors.identityType }}</text>
        </view>
      </view>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- STEP 1  基础信息                                    -->
      <!-- ═══════════════════════════════════════════════════ -->
      <view v-if="currentStep === 1" class="step-content">
        <!-- 个人信息 -->
        <view class="card">
          <text class="card-title">个人信息</text>

          <view class="form-item">
            <text class="label required">真实姓名</text>
            <input
              v-model="form.realName"
              class="input"
              :class="{ 'input-error': errors.realName }"
              placeholder="请输入真实姓名"
              maxlength="10"
            />
            <text v-if="errors.realName" class="err-msg">{{ errors.realName }}</text>
          </view>

          <view class="form-item">
            <text class="label required">身份证号</text>
            <input
              v-model="form.idCardNo"
              class="input"
              :class="{ 'input-error': errors.idCardNo }"
              placeholder="请输入18位身份证号码"
              maxlength="18"
            />
            <text v-if="errors.idCardNo" class="err-msg">{{ errors.idCardNo }}</text>
          </view>

          <view class="form-item">
            <text class="label required">性别</text>
            <view class="radio-group">
              <view
                class="radio-item"
                :class="{ checked: form.gender === 1 }"
                @tap="form.gender = 1"
              >
                <view class="radio-dot" />
                <text>男</text>
              </view>
              <view
                class="radio-item"
                :class="{ checked: form.gender === 2 }"
                @tap="form.gender = 2"
              >
                <view class="radio-dot" />
                <text>女</text>
              </view>
            </view>
            <text v-if="errors.gender" class="err-msg">{{ errors.gender }}</text>
          </view>

          <view class="form-item">
            <text class="label required">出生日期</text>
            <view
              class="picker-row"
              :class="{ 'input-error': errors.birthday }"
              @tap="showDatePicker = true"
            >
              <text :class="form.birthday ? 'picker-val' : 'picker-placeholder'">
                {{ form.birthday || '请选择出生日期' }}
              </text>
              <u-icon name="arrow-right" size="14" color="#c0c4cc" />
            </view>
            <u-datetime-picker
              :show="showDatePicker"
              v-model="birthdayTimestamp"
              mode="date"
              :max-date="maxBirthday"
              @confirm="onBirthdayConfirm"
              @cancel="showDatePicker = false"
            />
            <text v-if="errors.birthday" class="err-msg">{{ errors.birthday }}</text>
          </view>

          <view class="form-item">
            <text class="label required">手机号</text>
            <input
              v-model="form.mobile"
              class="input"
              :class="{ 'input-error': errors.mobile }"
              placeholder="请输入手机号"
              type="number"
              maxlength="11"
            />
            <text v-if="errors.mobile" class="err-msg">{{ errors.mobile }}</text>
          </view>

          <view class="form-item">
            <text class="label required">所在城市</text>
            <input
              v-model="form.city"
              class="input"
              :class="{ 'input-error': errors.city }"
              placeholder="如：北京市海淀区"
              maxlength="30"
            />
            <text v-if="errors.city" class="err-msg">{{ errors.city }}</text>
          </view>
        </view>

        <!-- 教育背景 -->
        <view class="card">
          <text class="card-title">教育背景</text>

          <view class="form-item">
            <text class="label required">毕业/就读院校</text>
            <input
              v-model="form.school"
              class="input"
              :class="{ 'input-error': errors.school }"
              placeholder="请输入院校全称"
              maxlength="50"
            />
            <text v-if="errors.school" class="err-msg">{{ errors.school }}</text>
          </view>

          <view class="form-item">
            <text class="label required">最高学历</text>
            <view
              class="picker-row"
              :class="{ 'input-error': errors.education }"
              @tap="showEducationPicker = true"
            >
              <text :class="form.education ? 'picker-val' : 'picker-placeholder'">
                {{ form.education || '请选择学历' }}
              </text>
              <u-icon name="arrow-right" size="14" color="#c0c4cc" />
            </view>
            <u-picker
              :show="showEducationPicker"
              :columns="educationColumns"
              @confirm="onEducationConfirm"
              @cancel="showEducationPicker = false"
            />
            <text v-if="errors.education" class="err-msg">{{ errors.education }}</text>
          </view>

          <view class="form-item">
            <text class="label required">专业方向</text>
            <input
              v-model="form.major"
              class="input"
              :class="{ 'input-error': errors.major }"
              placeholder="如：数学教育、心理学"
              maxlength="30"
            />
            <text v-if="errors.major" class="err-msg">{{ errors.major }}</text>
          </view>
        </view>

        <!-- 服务信息 -->
        <view class="card">
          <text class="card-title">服务信息</text>

          <view class="form-item">
            <text class="label required">擅长科目</text>
            <view class="tag-group">
              <view
                v-for="s in subjectOptions"
                :key="s"
                class="tag-item"
                :class="{ selected: form.subjects.includes(s) }"
                @tap="toggleSubject(s)"
              >{{ s }}</view>
            </view>
            <text v-if="errors.subjects" class="err-msg">{{ errors.subjects }}</text>
          </view>

          <view class="form-item">
            <text class="label required">擅长年级</text>
            <view class="tag-group">
              <view
                v-for="g in gradeOptions"
                :key="g"
                class="tag-item"
                :class="{ selected: form.grades.includes(g) }"
                @tap="toggleGrade(g)"
              >{{ g }}</view>
            </view>
            <text v-if="errors.grades" class="err-msg">{{ errors.grades }}</text>
          </view>

          <view class="form-item">
            <text class="label required">服务单价（元/小时）</text>
            <view class="price-input-row" :class="{ 'input-error': errors.servicePrice }">
              <text class="price-symbol">¥</text>
              <input
                v-model="priceInput"
                class="price-input"
                placeholder="请输入"
                type="digit"
                maxlength="6"
                @blur="onPriceBlur"
              />
              <text class="price-unit">元 / 小时</text>
            </view>
            <text class="form-tip">平台收取 10% 服务费，实际到手为报价的 90%</text>
            <text v-if="errors.servicePrice" class="err-msg">{{ errors.servicePrice }}</text>
          </view>

          <view class="form-item">
            <text class="label required">个人简介</text>
            <view class="textarea-wrap" :class="{ 'input-error': errors.introduction }">
              <textarea
                v-model="form.introduction"
                class="textarea"
                placeholder="请介绍您的教学经历、特长和服务理念，不少于50字"
                maxlength="500"
                auto-height
              />
              <text class="word-count">{{ form.introduction.length }}/500</text>
            </view>
            <text v-if="errors.introduction" class="err-msg">{{ errors.introduction }}</text>
          </view>
        </view>
      </view>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- STEP 2  证件上传 & 人脸核验                         -->
      <!-- ═══════════════════════════════════════════════════ -->
      <view v-if="currentStep === 2" class="step-content">

        <!-- 身份证 -->
        <view class="card">
          <text class="card-title">身份证上传</text>
          <text class="card-desc">请上传本人身份证，图片须清晰、完整，不得遮挡</text>
          <view class="id-card-row">
            <view class="id-upload-box" @tap="pickIdCard('front')">
              <image
                v-if="idCardFrontUrl"
                :src="idCardFrontUrl"
                class="id-preview"
                mode="aspectFill"
                @tap.stop="previewImage(idCardFrontUrl)"
              />
              <template v-else>
                <view class="id-card-bg front">
                  <text class="id-bg-text">居民身份证</text>
                  <view class="id-camera-hint">
                    <u-icon name="camera" size="24" color="#3b82f6" />
                    <text>点击上传正面</text>
                  </view>
                </view>
              </template>
              <text class="upload-label">身份证正面</text>
            </view>
            <view class="id-upload-box" @tap="pickIdCard('back')">
              <image
                v-if="idCardBackUrl"
                :src="idCardBackUrl"
                class="id-preview"
                mode="aspectFill"
                @tap.stop="previewImage(idCardBackUrl)"
              />
              <template v-else>
                <view class="id-card-bg back">
                  <text class="id-bg-text">国徽面</text>
                  <view class="id-camera-hint">
                    <u-icon name="camera" size="24" color="#3b82f6" />
                    <text>点击上传背面</text>
                  </view>
                </view>
              </template>
              <text class="upload-label">身份证背面</text>
            </view>
          </view>
          <text v-if="errors.idCard" class="err-msg">{{ errors.idCard }}</text>
        </view>

        <!-- 资质证明 -->
        <view class="card">
          <view class="card-title-row">
            <text class="card-title">资质证明</text>
            <text class="card-badge">{{ qualificationLabel }}</text>
          </view>
          <text class="card-desc">{{ qualificationDesc }}</text>
          <view class="upload-grid">
            <view
              v-for="(item, i) in qualificationItems"
              :key="i"
              class="upload-cell"
              @tap="pickQualification(i)"
            >
              <image
                v-if="item.url"
                :src="item.url"
                class="upload-preview"
                mode="aspectFill"
                @tap.stop="previewImage(item.url)"
              />
              <template v-else>
                <u-icon name="plus" size="28" color="#c0c4cc" />
                <text class="upload-cell-text">添加图片</text>
              </template>
              <view v-if="item.url" class="delete-btn" @tap.stop="removeQualification(i)">
                <u-icon name="close" size="12" color="#fff" />
              </view>
            </view>
            <view
              v-if="qualificationItems.filter(i => i.url).length < 5"
              class="upload-cell add-more"
              @tap="addQualificationSlot"
            >
              <u-icon name="plus" size="28" color="#3b82f6" />
              <text class="upload-cell-text" style="color: #3b82f6;">继续添加</text>
            </view>
          </view>
          <text v-if="errors.qualifications" class="err-msg">{{ errors.qualifications }}</text>
        </view>

        <!-- 无犯罪记录证明 -->
        <view class="card">
          <view class="card-title-row">
            <text class="card-title">无犯罪记录证明</text>
            <text class="required-tag">必须上传</text>
          </view>
          <text class="card-desc">请到户籍所在地派出所或政务网开具，有效期6个月内</text>
          <view class="single-upload-box" @tap="pickCriminalRecord">
            <image
              v-if="criminalRecordUrl"
              :src="criminalRecordUrl"
              class="single-preview"
              mode="aspectFill"
              @tap.stop="previewImage(criminalRecordUrl)"
            />
            <template v-else>
              <view class="single-upload-inner">
                <view class="upload-icon-wrap">
                  <u-icon name="upload" size="36" color="#3b82f6" />
                </view>
                <text class="single-upload-text">点击上传无犯罪记录证明</text>
                <text class="single-upload-hint">支持 JPG / PNG，最大 10MB</text>
              </view>
            </template>
          </view>
          <text v-if="errors.criminalRecord" class="err-msg">{{ errors.criminalRecord }}</text>
        </view>

        <!-- 人脸核验 -->
        <view class="card">
          <view class="card-title-row">
            <text class="card-title">人脸核验</text>
            <view class="verify-status-tag" :class="faceVerifyStatusClass">
              <u-icon :name="faceVerifyStatusIcon" size="14" :color="faceVerifyStatusColor" />
              <text>{{ faceVerifyStatusText }}</text>
            </view>
          </view>
          <text class="card-desc">通过人脸识别核验您的真实身份，保障平台安全</text>
          <view class="face-verify-box" @tap="startFaceVerifyFlow">
            <view class="face-icon-wrap">
              <u-icon name="scan" size="48" color="#3b82f6" />
            </view>
            <view class="face-info">
              <text class="face-title">{{ form.faceVerified ? '核验已通过 ✅' : '点击开始人脸核验' }}</text>
              <text class="face-desc">{{ form.faceVerified ? '您的身份已通过实名核验' : '需调用微信人脸识别，约30秒完成' }}</text>
            </view>
            <u-icon v-if="!form.faceVerified" name="arrow-right" size="18" color="#3b82f6" />
          </view>
          <text v-if="errors.faceVerify" class="err-msg">{{ errors.faceVerify }}</text>
        </view>
      </view>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- STEP 3  承诺书 & 提交                               -->
      <!-- ═══════════════════════════════════════════════════ -->
      <view v-if="currentStep === 3" class="step-content">
        <view class="card">
          <text class="card-title">陪伴师服务承诺书</text>
          <scroll-view scroll-y class="terms-scroll">
            <text class="terms-text">{{ termsContent }}</text>
          </scroll-view>
        </view>

        <!-- 申请信息预览 -->
        <view class="card">
          <text class="card-title">申请信息确认</text>
          <view class="preview-row" v-for="item in previewItems" :key="item.label">
            <text class="preview-label">{{ item.label }}</text>
            <text class="preview-val">{{ item.value }}</text>
          </view>
        </view>

        <!-- 承诺书勾选 -->
        <view class="card">
          <view class="checkbox-row" @tap="form.agreeTerms = !form.agreeTerms">
            <view class="checkbox" :class="{ checked: form.agreeTerms }">
              <u-icon v-if="form.agreeTerms" name="checkmark" size="14" color="#fff" />
            </view>
            <text class="checkbox-text">
              我已认真阅读以上承诺书，承诺以上填写信息真实有效，并同意遵守平台
              <text class="link">《陪伴师服务协议》</text>
              及
              <text class="link">《平台行为准则》</text>
            </text>
          </view>
          <text v-if="errors.agreeTerms" class="err-msg">{{ errors.agreeTerms }}</text>

          <view class="tips-box">
            <u-icon name="info-circle" size="14" color="#f59e0b" />
            <text class="tips-text">虚假信息将导致申请驳回，情节严重者将被永久封禁</text>
          </view>
        </view>

        <!-- 提交按钮 -->
        <view class="submit-area">
          <button
            class="btn-submit"
            :loading="submitting"
            :disabled="submitting"
            @tap="handleSubmit"
          >
            {{ submitting ? '提交中...' : '提交申请' }}
          </button>
        </view>
      </view>

    </scroll-view>

    <!-- ── 底部导航按钮 ────────────────────────────────────── -->
    <view class="bottom-bar" v-if="currentStep < 3">
      <button v-if="currentStep > 0" class="btn-prev" @tap="prevStep">上一步</button>
      <button
        class="btn-next"
        :class="{ full: currentStep === 0 }"
        @tap="nextStep"
      >
        {{ currentStep === 2 ? '下一步，确认提交' : '下一步' }}
      </button>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import {
  submitTutorApply,
  uploadTutorFile,
  startFaceVerify,
  checkFaceVerify,
  type TutorIdentityType
} from '@/api/tutor'
import { isMobile, isIdCard } from '@/utils/validate'

// ── 步骤 ──────────────────────────────────────────────────────────
const steps = ['身份选择', '基础信息', '证件上传', '确认提交']
const currentStep = ref(0)
const scrollTop = ref(0)

// ── 身份选项 ─────────────────────────────────────────────────────
const identityOptions = [
  { value: 'college_student' as TutorIdentityType, label: '在读大学生', desc: '985/211 优先，需提供学生证', icon: 'book', bg: '#eff6ff', color: '#3b82f6' },
  { value: 'teacher' as TutorIdentityType, label: '现/在职教师', desc: '国家认证教师资格证', icon: 'account', bg: '#f0fdf4', color: '#22c55e' },
  { value: 'psychologist' as TutorIdentityType, label: '心理咨询师', desc: '国家二级/三级心理咨询师', icon: 'heart', bg: '#fdf2f8', color: '#ec4899' },
  { value: 'other' as TutorIdentityType, label: '其他专业人士', desc: '具备相关专业背景或从业经验', icon: 'star', bg: '#fef9c3', color: '#eab308' }
]

// ── 选项 ─────────────────────────────────────────────────────────
const educationColumns = [['高中', '大专', '本科', '硕士', '博士']]
const subjectOptions = ['语文', '数学', '英语', '物理', '化学', '生物', '历史', '地理', '政治', '编程', '音乐', '美术', '体育']
const gradeOptions = ['小学低年级', '小学高年级', '初一', '初二', '初三', '高一', '高二', '高三']

// ── 资质证明条目 ──────────────────────────────────────────────────
interface QualItem { key: string; url: string }
const qualificationItems = ref<QualItem[]>([{ key: '', url: '' }])

const qualificationLabel = computed(() => {
  const m: Partial<Record<TutorIdentityType, string>> = {
    college_student: '学生证', teacher: '教师资格证', psychologist: '心理咨询师证', other: '相关从业证明'
  }
  return m[form.identityType as TutorIdentityType] || '资质证明'
})

const qualificationDesc = computed(() => {
  const m: Partial<Record<TutorIdentityType, string>> = {
    college_student: '请上传本人学生证（学校+姓名+学号页），确保清晰可辨',
    teacher: '请上传教师资格证正反面，可上传多张（最多5张）',
    psychologist: '请上传心理咨询师资格证书，可上传多张（最多5张）',
    other: '请上传能证明您专业背景的证书或材料，最多上传5张'
  }
  return m[form.identityType as TutorIdentityType] || '请上传相关资质证明'
})

// ── 图片 URL ─────────────────────────────────────────────────────
const idCardFrontUrl = ref('')
const idCardBackUrl = ref('')
const criminalRecordUrl = ref('')

// ── 人脸核验状态 ──────────────────────────────────────────────────
const faceVerifying = ref(false)
const faceVerifyStatusClass = computed(() => form.faceVerified ? 'verified' : 'unverified')
const faceVerifyStatusIcon = computed(() => form.faceVerified ? 'checkmark-circle' : 'warning')
const faceVerifyStatusColor = computed(() => form.faceVerified ? '#22c55e' : '#f59e0b')
const faceVerifyStatusText = computed(() => form.faceVerified ? '已核验' : '待核验')

// ── Picker ────────────────────────────────────────────────────────
const showDatePicker = ref(false)
const showEducationPicker = ref(false)
const birthdayTimestamp = ref(Date.now() - 20 * 365 * 24 * 3600 * 1000)
const maxBirthday = Date.now() - 18 * 365 * 24 * 3600 * 1000

// ── 价格输入 ─────────────────────────────────────────────────────
const priceInput = ref('')
function onPriceBlur() {
  const val = parseFloat(priceInput.value)
  form.servicePrice = isNaN(val) ? 0 : val
}

// ── 表单 ─────────────────────────────────────────────────────────
const form = reactive({
  identityType: '' as TutorIdentityType | '',
  realName: '',
  idCardNo: '',
  gender: 0,
  birthday: '',
  mobile: '',
  city: '',
  school: '',
  education: '',
  major: '',
  subjects: [] as string[],
  grades: [] as string[],
  servicePrice: 0,
  introduction: '',
  idCardFrontKey: '',
  idCardBackKey: '',
  qualificationKeys: [] as string[],
  criminalRecordKey: '',
  faceVerified: false,
  agreeTerms: false
})

// ── 校验 ─────────────────────────────────────────────────────────
const errors = reactive<Record<string, string>>({})
function clearErr(f: string) { delete errors[f] }

function validateStep0() {
  let ok = true
  if (!form.identityType) { errors.identityType = '请选择您的身份类型'; ok = false }
  return ok
}

function validateStep1() {
  let ok = true
  if (!form.realName.trim()) { errors.realName = '请输入真实姓名'; ok = false }
  if (!isIdCard(form.idCardNo)) { errors.idCardNo = '请输入有效的18位身份证号码'; ok = false }
  if (!form.gender) { errors.gender = '请选择性别'; ok = false }
  if (!form.birthday) { errors.birthday = '请选择出生日期'; ok = false }
  if (!isMobile(form.mobile)) { errors.mobile = '请输入有效的手机号'; ok = false }
  if (!form.city.trim()) { errors.city = '请输入所在城市'; ok = false }
  if (!form.school.trim()) { errors.school = '请输入院校名称'; ok = false }
  if (!form.education) { errors.education = '请选择最高学历'; ok = false }
  if (!form.major.trim()) { errors.major = '请输入专业方向'; ok = false }
  if (form.subjects.length === 0) { errors.subjects = '请至少选择1个擅长科目'; ok = false }
  if (form.grades.length === 0) { errors.grades = '请至少选择1个擅长年级'; ok = false }
  if (!form.servicePrice || form.servicePrice < 30) { errors.servicePrice = '服务单价不能低于30元/小时'; ok = false }
  if (form.introduction.trim().length < 50) { errors.introduction = '个人简介不少于50字'; ok = false }
  return ok
}

function validateStep2() {
  let ok = true
  if (!form.idCardFrontKey || !form.idCardBackKey) { errors.idCard = '请上传身份证正面和背面'; ok = false }
  if (form.qualificationKeys.length === 0) { errors.qualifications = '请至少上传1张资质证明'; ok = false }
  if (!form.criminalRecordKey) { errors.criminalRecord = '请上传无犯罪记录证明'; ok = false }
  if (!form.faceVerified) { errors.faceVerify = '请完成人脸核验'; ok = false }
  return ok
}

// ── 身份选择 ─────────────────────────────────────────────────────
function selectIdentity(val: TutorIdentityType) {
  form.identityType = val
  clearErr('identityType')
}

// ── 多选 ─────────────────────────────────────────────────────────
function toggleSubject(s: string) {
  const idx = form.subjects.indexOf(s)
  idx === -1 ? form.subjects.push(s) : form.subjects.splice(idx, 1)
  clearErr('subjects')
}

function toggleGrade(g: string) {
  const idx = form.grades.indexOf(g)
  idx === -1 ? form.grades.push(g) : form.grades.splice(idx, 1)
  clearErr('grades')
}

// ── Picker 回调 ───────────────────────────────────────────────────
function onBirthdayConfirm(e: { value: number }) {
  const d = new Date(e.value)
  form.birthday = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
  showDatePicker.value = false
  clearErr('birthday')
}

function onEducationConfirm(e: { value: string[] }) {
  form.education = e.value[0]
  showEducationPicker.value = false
  clearErr('education')
}

// ── 图片选取 & 上传 ───────────────────────────────────────────────
async function uploadImage(type: string) {
  return new Promise<{ key: string; url: string } | null>((resolve) => {
    uni.chooseImage({
      count: 1,
      sizeType: ['compressed'],
      sourceType: ['album', 'camera'],
      success: async (res) => {
        try {
          uni.showLoading({ title: '上传中...', mask: true })
          const result = await uploadTutorFile(res.tempFilePaths[0], type)
          resolve({ key: result.data.fileKey, url: result.data.fileUrl })
        } catch {
          uni.showToast({ title: '上传失败，请重试', icon: 'none' })
          resolve(null)
        } finally {
          uni.hideLoading()
        }
      },
      fail: () => resolve(null)
    })
  })
}

async function pickIdCard(side: 'front' | 'back') {
  const res = await uploadImage(`id_card_${side}`)
  if (!res) return
  if (side === 'front') { form.idCardFrontKey = res.key; idCardFrontUrl.value = res.url }
  else { form.idCardBackKey = res.key; idCardBackUrl.value = res.url }
  clearErr('idCard')
}

async function pickQualification(index: number) {
  const res = await uploadImage('qualification')
  if (!res) return
  qualificationItems.value[index] = { key: res.key, url: res.url }
  form.qualificationKeys = qualificationItems.value.filter((i) => i.key).map((i) => i.key)
  clearErr('qualifications')
}

function removeQualification(index: number) {
  qualificationItems.value[index] = { key: '', url: '' }
  form.qualificationKeys = qualificationItems.value.filter((i) => i.key).map((i) => i.key)
}

function addQualificationSlot() {
  const filled = qualificationItems.value.filter((i) => i.url).length
  if (filled < 5) qualificationItems.value.push({ key: '', url: '' })
}

async function pickCriminalRecord() {
  const res = await uploadImage('criminal_record')
  if (!res) return
  form.criminalRecordKey = res.key
  criminalRecordUrl.value = res.url
  clearErr('criminalRecord')
}

function previewImage(url: string) {
  if (!url) return
  uni.previewImage({ urls: [url], current: url })
}

// ── 人脸核验 ─────────────────────────────────────────────────────
async function startFaceVerifyFlow() {
  if (form.faceVerified) { uni.showToast({ title: '已完成人脸核验', icon: 'success' }); return }
  if (faceVerifying.value) return
  faceVerifying.value = true
  try {
    uni.showLoading({ title: '准备核验...', mask: true })
    const res = await startFaceVerify()
    const { verifyToken } = res.data
    uni.hideLoading()
    uni.showModal({
      title: '人脸核验',
      content: '即将调起人脸识别，请保持光线充足并正对摄像头，点击确认开始',
      confirmText: '开始核验',
      success: async (modal) => {
        if (!modal.confirm) { faceVerifying.value = false; return }
        uni.showLoading({ title: '核验中...', mask: true })
        // 实际项目中在此处调用 wx.startFacialRecognitionVerify
        await new Promise((r) => setTimeout(r, 2000))
        const checkRes = await checkFaceVerify(verifyToken)
        uni.hideLoading()
        if (checkRes.data.verified) {
          form.faceVerified = true
          clearErr('faceVerify')
          uni.showToast({ title: '核验通过 ✅', icon: 'success' })
        } else {
          uni.showToast({ title: '核验未通过，请重试', icon: 'none' })
        }
        faceVerifying.value = false
      }
    })
  } catch {
    uni.hideLoading()
    faceVerifying.value = false
    uni.showToast({ title: '核验服务暂时不可用', icon: 'none' })
  }
}

// ── 步骤导航 ─────────────────────────────────────────────────────
function nextStep() {
  Object.keys(errors).forEach((k) => delete errors[k])
  const validators = [validateStep0, validateStep1, validateStep2]
  const valid = (validators[currentStep.value] ?? (() => true))()
  if (!valid) return
  currentStep.value++
  scrollTop.value = 0
  setTimeout(() => { scrollTop.value = 0 }, 50)
}

function prevStep() {
  if (currentStep.value > 0) { currentStep.value--; scrollTop.value = 0 }
}

// ── 提交 ─────────────────────────────────────────────────────────
const submitting = ref(false)

async function handleSubmit() {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!form.agreeTerms) { errors.agreeTerms = '请阅读并同意承诺书后继续'; return }
  if (submitting.value) return
  submitting.value = true
  try {
    await submitTutorApply({
      ...form,
      identityType: form.identityType as TutorIdentityType,
      qualificationKeys: form.qualificationKeys
    })
    uni.showModal({
      title: '申请已提交 🎉',
      content: '我们将在3个工作日内完成审核，请留意消息通知',
      showCancel: false,
      confirmText: '知道了',
      success: () => uni.navigateBack({ delta: 1 })
    })
  } catch {
    // 错误已由 request 统一处理
  } finally {
    submitting.value = false
  }
}

// ── 信息预览 ─────────────────────────────────────────────────────
const previewItems = computed(() => [
  { label: '身份类型', value: identityOptions.find((o) => o.value === form.identityType)?.label ?? '-' },
  { label: '真实姓名', value: form.realName || '-' },
  { label: '性别', value: form.gender === 1 ? '男' : form.gender === 2 ? '女' : '-' },
  { label: '联系手机', value: form.mobile ? `${form.mobile.slice(0, 3)}****${form.mobile.slice(7)}` : '-' },
  { label: '所在城市', value: form.city || '-' },
  { label: '毕业院校', value: form.school || '-' },
  { label: '最高学历', value: form.education || '-' },
  { label: '擅长科目', value: form.subjects.join('、') || '-' },
  { label: '服务单价', value: form.servicePrice ? `¥ ${form.servicePrice} 元/小时` : '-' },
  { label: '人脸核验', value: form.faceVerified ? '已通过 ✅' : '未核验 ❌' }
])

// ── 承诺书 ────────────────────────────────────────────────────────
const termsContent = `陪伴师服务承诺书

一、本人承诺填写的所有信息（姓名、身份证号、学历、资质证明等）均真实有效，如有虚假，愿承担相应法律责任并接受平台封号处理。

二、本人承诺严格遵守国家法律法规，不得对未成年用户实施任何形式的言语骚扰、诱导、欺骗或伤害。

三、本人承诺在服务过程中保持专业、友善、耐心，以学生的学习进步和心理健康为首要目标。

四、本人承诺不在平台之外与学生或家长私自联系，不绕过平台私自收取费用。

五、本人了解并同意，平台有权在接到投诉后暂停本人服务资质，调查确认后做出相应处罚。

六、本人承诺在无犯罪记录证明到期6个月前主动更新证明材料，维持资质有效性。`

onLoad(() => { /* 草稿回填预留 */ })
</script>

<style lang="scss" scoped>
.apply-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: $bg-page;
}

/* ── 步骤条 ─────────────────────────────────────────────────── */
.steps-header {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  background: #fff;
  padding: 28rpx 24rpx 20rpx;
  box-shadow: $shadow-sm;
  flex-shrink: 0;
  position: relative;
  z-index: 10;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
}

.step-circle {
  width: 52rpx;
  height: 52rpx;
  border-radius: 50%;
  background: #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: $font-sm;
  color: #9ca3af;
  font-weight: 600;
  transition: all 0.3s;
  z-index: 1;

  .step-item.active & { background: $primary-color; color: #fff; box-shadow: 0 4rpx 12rpx rgba(59,130,246,0.4); }
  .step-item.done &   { background: $success-color; color: #fff; }
}

.step-label {
  font-size: 20rpx;
  color: #9ca3af;
  margin-top: 8rpx;

  .step-item.active & { color: $primary-color; font-weight: 600; }
  .step-item.done &   { color: $success-color; }
}

.step-line {
  position: absolute;
  top: 26rpx;
  left: calc(50% + 26rpx);
  right: calc(-50% + 26rpx);
  height: 2rpx;
  background: #e5e7eb;
  z-index: 0;
  &.done { background: $success-color; }
}

/* ── 滚动区 ─────────────────────────────────────────────────── */
.form-scroll { flex: 1; overflow: hidden; }

.step-content {
  padding: 24rpx 24rpx 160rpx;
  display: flex;
  flex-direction: column;
  gap: 24rpx;
}

/* ── 卡片 ───────────────────────────────────────────────────── */
.card {
  background: #fff;
  border-radius: $radius-lg;
  padding: $spacing-lg;
  box-shadow: $shadow-sm;
}

.card-title {
  font-size: $font-lg;
  font-weight: 700;
  color: $text-primary;
  display: block;
  margin-bottom: 8rpx;
}

.card-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8rpx;
}

.card-desc {
  font-size: $font-xs;
  color: $text-secondary;
  line-height: 1.6;
  display: block;
  margin-bottom: 20rpx;
}

.card-badge {
  font-size: $font-xs;
  background: $primary-light;
  color: $primary-color;
  padding: 4rpx 16rpx;
  border-radius: $radius-full;
}

.required-tag {
  font-size: $font-xs;
  background: #fef2f2;
  color: $danger-color;
  padding: 4rpx 16rpx;
  border-radius: $radius-full;
}

/* ── 身份选择 ───────────────────────────────────────────────── */
.identity-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16rpx;
  margin-top: 16rpx;
}

.identity-card {
  border: 2rpx solid $border-color;
  border-radius: $radius-md;
  padding: 24rpx 16rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8rpx;
  position: relative;
  transition: all 0.25s;

  &.selected { border-color: $primary-color; background: $primary-light; }
}

.id-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: $radius-md;
  display: flex;
  align-items: center;
  justify-content: center;
}

.id-label { font-size: $font-base; font-weight: 600; color: $text-primary; }
.id-desc { font-size: 20rpx; color: $text-secondary; text-align: center; line-height: 1.4; }
.id-check { position: absolute; top: 12rpx; right: 12rpx; }

/* ── 表单项 ─────────────────────────────────────────────────── */
.form-item {
  margin-bottom: 28rpx;
  &:last-child { margin-bottom: 0; }
}

.label {
  font-size: $font-sm;
  color: $text-secondary;
  display: block;
  margin-bottom: 12rpx;
  &.required::before { content: '* '; color: $danger-color; }
}

.input {
  height: 80rpx;
  background: #f8faff;
  border: 1rpx solid $border-color;
  border-radius: $radius-md;
  padding: 0 24rpx;
  font-size: $font-base;
  color: $text-primary;
  &.input-error { border-color: $danger-color; background: #fff5f5; }
}

.picker-row {
  height: 80rpx;
  background: #f8faff;
  border: 1rpx solid $border-color;
  border-radius: $radius-md;
  padding: 0 24rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;
  &.input-error { border-color: $danger-color; }
}

.picker-val { font-size: $font-base; color: $text-primary; }
.picker-placeholder { font-size: $font-base; color: $text-placeholder; }

/* ── 性别 ───────────────────────────────────────────────────── */
.radio-group { display: flex; gap: 32rpx; }

.radio-item {
  display: flex;
  align-items: center;
  gap: 12rpx;
  font-size: $font-base;
  color: $text-regular;
}

.radio-dot {
  width: 36rpx;
  height: 36rpx;
  border-radius: 50%;
  border: 2rpx solid $border-color;
  background: #f8faff;
  transition: all 0.2s;

  .radio-item.checked & {
    background: $primary-color;
    border-color: $primary-color;
    box-shadow: 0 0 0 4rpx rgba(59,130,246,0.2);
  }
}

/* ── 多选标签 ───────────────────────────────────────────────── */
.tag-group { display: flex; flex-wrap: wrap; gap: 12rpx; }

.tag-item {
  padding: 10rpx 24rpx;
  border-radius: $radius-full;
  font-size: $font-sm;
  color: $text-secondary;
  background: #f4f6f8;
  border: 1rpx solid transparent;
  transition: all 0.2s;

  &.selected { background: $primary-light; color: $primary-color; border-color: $primary-color; font-weight: 600; }
}

/* ── 价格 ───────────────────────────────────────────────────── */
.price-input-row {
  display: flex;
  align-items: center;
  gap: 12rpx;
  background: #f8faff;
  border: 1rpx solid $border-color;
  border-radius: $radius-md;
  padding: 0 20rpx;
  height: 80rpx;
  &.input-error { border-color: $danger-color; }
}

.price-symbol { font-size: $font-xl; color: $danger-color; font-weight: 700; }

.price-input {
  flex: 1;
  height: 100%;
  background: transparent;
  border: none;
  padding: 0 8rpx;
  font-size: $font-xl;
  font-weight: 700;
  color: $danger-color;
}

.price-unit { font-size: $font-sm; color: $text-secondary; }

.form-tip { font-size: 20rpx; color: $text-secondary; margin-top: 8rpx; display: block; }

/* ── 文本域 ─────────────────────────────────────────────────── */
.textarea-wrap {
  background: #f8faff;
  border: 1rpx solid $border-color;
  border-radius: $radius-md;
  padding: 20rpx 24rpx;
  position: relative;
  &.input-error { border-color: $danger-color; }
}

.textarea { width: 100%; font-size: $font-base; color: $text-primary; line-height: 1.7; min-height: 180rpx; }
.word-count { font-size: 20rpx; color: $text-placeholder; text-align: right; display: block; margin-top: 8rpx; }

/* ── 身份证上传 ─────────────────────────────────────────────── */
.id-card-row { display: flex; gap: 20rpx; margin-top: 8rpx; }

.id-upload-box { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 12rpx; }

.id-card-bg {
  width: 100%;
  aspect-ratio: 85.6 / 54;
  border-radius: $radius-md;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
  position: relative;

  &.front { background: linear-gradient(135deg, #1a3a5c, #0f2340); }
  &.back  { background: linear-gradient(135deg, #2c2c2c, #1a1a1a); }
}

.id-bg-text { font-size: $font-xs; color: rgba(255,255,255,0.5); position: absolute; top: 16rpx; left: 20rpx; }

.id-camera-hint {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8rpx;
  font-size: $font-xs;
  color: rgba(255,255,255,0.7);
}

.id-preview { width: 100%; aspect-ratio: 85.6 / 54; border-radius: $radius-md; }
.upload-label { font-size: $font-xs; color: $text-secondary; }

/* ── 上传网格 ───────────────────────────────────────────────── */
.upload-grid { display: flex; flex-wrap: wrap; gap: 16rpx; margin-top: 12rpx; }

.upload-cell {
  width: 180rpx;
  height: 180rpx;
  border-radius: $radius-md;
  border: 2rpx dashed $border-color;
  background: #f8faff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
  position: relative;
  overflow: hidden;

  &.add-more { border-color: $primary-color; background: $primary-light; }
}

.upload-cell-text { font-size: 20rpx; color: $text-placeholder; }
.upload-preview { width: 100%; height: 100%; }

.delete-btn {
  position: absolute;
  top: 6rpx;
  right: 6rpx;
  width: 36rpx;
  height: 36rpx;
  border-radius: 50%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── 单文件上传 ─────────────────────────────────────────────── */
.single-upload-box {
  border: 2rpx dashed $border-color;
  border-radius: $radius-md;
  background: #f8faff;
  overflow: hidden;
  margin-top: 12rpx;
}

.single-upload-inner {
  padding: 48rpx 32rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16rpx;
}

.upload-icon-wrap {
  width: 100rpx;
  height: 100rpx;
  background: $primary-light;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.single-upload-text { font-size: $font-base; color: $text-regular; font-weight: 500; }
.single-upload-hint { font-size: $font-xs; color: $text-placeholder; }
.single-preview { width: 100%; height: 400rpx; }

/* ── 人脸核验 ───────────────────────────────────────────────── */
.verify-status-tag {
  display: flex;
  align-items: center;
  gap: 6rpx;
  padding: 6rpx 16rpx;
  border-radius: $radius-full;
  font-size: 22rpx;

  &.verified { background: #f0fdf4; color: $success-color; }
  &.unverified { background: #fffbeb; color: $warning-color; }
}

.face-verify-box {
  display: flex;
  align-items: center;
  gap: 20rpx;
  background: $primary-light;
  border-radius: $radius-md;
  padding: 24rpx;
  margin-top: 8rpx;
  border: 1rpx solid rgba(59,130,246,0.2);
}

.face-icon-wrap {
  width: 96rpx;
  height: 96rpx;
  background: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: $shadow-sm;
}

.face-info { flex: 1; display: flex; flex-direction: column; gap: 6rpx; }
.face-title { font-size: $font-base; font-weight: 600; color: $text-primary; }
.face-desc { font-size: $font-xs; color: $text-secondary; }

/* ── 承诺书 ─────────────────────────────────────────────────── */
.terms-scroll { height: 360rpx; background: #f8faff; border-radius: $radius-md; padding: 20rpx; margin-top: 12rpx; }
.terms-text { font-size: $font-xs; color: $text-secondary; line-height: 1.8; white-space: pre-wrap; }

/* ── 信息预览 ───────────────────────────────────────────────── */
.preview-row {
  display: flex;
  justify-content: space-between;
  padding: 16rpx 0;
  border-bottom: 1rpx solid $border-light;
  &:last-child { border-bottom: none; }
}

.preview-label { font-size: $font-sm; color: $text-secondary; }
.preview-val { font-size: $font-sm; color: $text-primary; font-weight: 500; max-width: 60%; text-align: right; }

/* ── 勾选 ───────────────────────────────────────────────────── */
.checkbox-row { display: flex; align-items: flex-start; gap: 16rpx; padding-bottom: 20rpx; }

.checkbox {
  width: 40rpx;
  height: 40rpx;
  border-radius: $radius-sm;
  border: 2rpx solid $border-color;
  background: #f8faff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 4rpx;
  transition: all 0.2s;

  &.checked { background: $primary-color; border-color: $primary-color; }
}

.checkbox-text { font-size: $font-sm; color: $text-regular; line-height: 1.7; flex: 1; }
.link { color: $primary-color; }

.tips-box {
  display: flex;
  align-items: flex-start;
  gap: 8rpx;
  background: #fffbeb;
  padding: 16rpx;
  border-radius: $radius-md;
  margin-top: 8rpx;
}

.tips-text { font-size: 22rpx; color: #92400e; line-height: 1.6; }

/* ── 提交 ───────────────────────────────────────────────────── */
.submit-area { padding-top: 8rpx; }

.btn-submit {
  width: 100%;
  height: 96rpx;
  background: linear-gradient(135deg, $primary-color, #6366f1);
  color: #fff;
  font-size: $font-lg;
  font-weight: 700;
  border-radius: $radius-full;
  border: none;
  box-shadow: 0 8rpx 24rpx rgba(59,130,246,0.4);
  &::after { border: none; }
  &[disabled] { opacity: 0.6; }
}

/* ── 底部按钮 ───────────────────────────────────────────────── */
.bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #fff;
  padding: 20rpx 32rpx;
  padding-bottom: calc(20rpx + constant(safe-area-inset-bottom));
  padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
  display: flex;
  gap: 20rpx;
  box-shadow: 0 -2rpx 16rpx rgba(0,0,0,0.06);
}

.btn-prev {
  flex: 1;
  height: 88rpx;
  background: #f4f6f8;
  color: $text-secondary;
  font-size: $font-base;
  border-radius: $radius-full;
  border: none;
  &::after { border: none; }
}

.btn-next {
  flex: 2;
  height: 88rpx;
  background: $primary-color;
  color: #fff;
  font-size: $font-base;
  font-weight: 600;
  border-radius: $radius-full;
  border: none;
  box-shadow: 0 4rpx 16rpx rgba(59,130,246,0.35);
  &.full { flex: 1; }
  &::after { border: none; }
}

/* ── 错误提示 ───────────────────────────────────────────────── */
.err-msg { font-size: 22rpx; color: $danger-color; margin-top: 8rpx; display: block; }
</style>
