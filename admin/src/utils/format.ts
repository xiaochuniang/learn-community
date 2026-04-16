/**
 * 格式化工具
 */

/** 日期时间格式化 */
export function formatDateTime(date: string | Date | null | undefined, fmt = 'YYYY-MM-DD HH:mm:ss'): string {
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

/** 金额格式化（分 → 元，保留2位小数） */
export function formatAmount(fen: number): string {
  return (fen / 100).toFixed(2)
}

/** 文件大小格式化 */
export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  if (bytes < 1024 * 1024 * 1024) return (bytes / 1024 / 1024).toFixed(1) + ' MB'
  return (bytes / 1024 / 1024 / 1024).toFixed(1) + ' GB'
}

/** 学习时长格式化（分钟 → x小时x分） */
export function formatStudyTime(minutes: number): string {
  if (minutes < 60) return `${minutes}分钟`
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return m > 0 ? `${h}小时${m}分钟` : `${h}小时`
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
