/** 日期时间格式化 */
export function formatDateTime(date: string | Date | null | undefined, fmt = 'YYYY-MM-DD HH:mm'): string {
  if (!date) return '-'
  const d = typeof date === 'string' ? new Date(date) : date
  if (isNaN(d.getTime())) return '-'
  const map: Record<string, string> = {
    YYYY: String(d.getFullYear()),
    MM: String(d.getMonth() + 1).padStart(2, '0'),
    DD: String(d.getDate()).padStart(2, '0'),
    HH: String(d.getHours()).padStart(2, '0'),
    mm: String(d.getMinutes()).padStart(2, '0'),
    ss: String(d.getSeconds()).padStart(2, '0')
  }
  return fmt.replace(/YYYY|MM|DD|HH|mm|ss/g, (m) => map[m])
}

/** 相对时间（刚刚 / x分钟前 等） */
export function timeAgo(date: string | Date): string {
  const d = typeof date === 'string' ? new Date(date) : date
  const diff = (Date.now() - d.getTime()) / 1000
  if (diff < 60) return '刚刚'
  if (diff < 3600) return `${Math.floor(diff / 60)}分钟前`
  if (diff < 86400) return `${Math.floor(diff / 3600)}小时前`
  if (diff < 2592000) return `${Math.floor(diff / 86400)}天前`
  return formatDateTime(d, 'MM-DD')
}

/** 金额：分 → 元，保留 2 位 */
export function formatAmount(fen: number): string {
  return (fen / 100).toFixed(2)
}

/** 元转显示字符串（如 "¥ 9.90"） */
export function priceText(yuan: number | string): string {
  const val = typeof yuan === 'string' ? parseFloat(yuan) : yuan
  return `¥ ${val.toFixed(2)}`
}

/** 手机号脱敏 */
export function maskMobile(mobile: string): string {
  if (!mobile || mobile.length < 11) return mobile
  return mobile.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2')
}

/** 截断文本 */
export function truncate(text: string, maxLen = 20): string {
  if (!text) return ''
  return text.length > maxLen ? text.slice(0, maxLen) + '…' : text
}

/** 学习时长：分钟 → "x小时x分钟" */
export function formatStudyTime(minutes: number): string {
  if (minutes < 60) return `${minutes}分钟`
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return m > 0 ? `${h}小时${m}分钟` : `${h}小时`
}

/** 文件大小 */
export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 ** 2) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1024 ** 2).toFixed(1)} MB`
}
