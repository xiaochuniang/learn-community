/** 手机号校验 */
export function isMobile(val: string): boolean {
  return /^1[3-9]\d{9}$/.test(val)
}

/** 是否为空（null / undefined / 空字符串 / 空数组 / 空对象） */
export function isEmpty(val: unknown): boolean {
  if (val === null || val === undefined) return true
  if (typeof val === 'string') return val.trim() === ''
  if (Array.isArray(val)) return val.length === 0
  if (typeof val === 'object') return Object.keys(val as object).length === 0
  return false
}

/** 数字范围检查 */
export function inRange(val: number, min: number, max: number): boolean {
  return val >= min && val <= max
}

/** URL 校验 */
export function isUrl(val: string): boolean {
  try {
    new URL(val)
    return true
  } catch {
    return false
  }
}

/** 身份证号校验（简单 18 位） */
export function isIdCard(val: string): boolean {
  return /^\d{17}[\dXx]$/.test(val)
}

/** 密码强度：至少 8 位、含字母和数字 */
export function isStrongPassword(val: string): boolean {
  return val.length >= 8 && /[a-zA-Z]/.test(val) && /\d/.test(val)
}
