/**
 * 校验工具
 */

/** 手机号 */
export function isMobile(val: string): boolean {
  return /^1[3-9]\d{9}$/.test(val)
}

/** 邮箱 */
export function isEmail(val: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)
}

/** 身份证号（18位） */
export function isIdCard(val: string): boolean {
  return /(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(val)
}

/** URL */
export function isUrl(val: string): boolean {
  try {
    new URL(val)
    return true
  } catch {
    return false
  }
}

/** 是否为空值 */
export function isEmpty(val: unknown): boolean {
  if (val === null || val === undefined) return true
  if (typeof val === 'string') return val.trim() === ''
  if (Array.isArray(val)) return val.length === 0
  if (typeof val === 'object') return Object.keys(val as object).length === 0
  return false
}

/** Element Plus Form 校验规则 — 必填 */
export const ruleRequired = (message = '该字段不能为空') => ({
  required: true,
  message,
  trigger: 'blur'
})

/** Element Plus Form 校验规则 — 手机号 */
export const ruleMobile = {
  validator: (_: unknown, value: string, callback: (err?: Error) => void) => {
    if (!value || isMobile(value)) {
      callback()
    } else {
      callback(new Error('请输入正确的手机号'))
    }
  },
  trigger: 'blur'
}
