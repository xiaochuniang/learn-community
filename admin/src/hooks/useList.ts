import { ref } from 'vue'

interface UseListOptions<Q> {
  fetchFn: (query: Q) => Promise<any>
  defaultQuery?: Partial<Q>
}

/**
 * 通用列表 Hook，封装 loading / 分页 / 查询逻辑
 */
export function useList<T = any, Q extends object = Record<string, any>>(
  options: UseListOptions<Q>
) {
  const { fetchFn } = options

  const loading = ref(false)
  const list = ref<T[]>([])
  const total = ref(0)
  const page = ref(1)
  const pageSize = ref(10)

  async function loadData(query: Q) {
    loading.value = true
    try {
      const res = await fetchFn({ ...query, page: page.value, pageSize: pageSize.value })
      list.value = res.data?.list ?? res.data ?? []
      total.value = res.data?.total ?? 0
    } finally {
      loading.value = false
    }
  }

  function handlePageChange(newPage: number) {
    page.value = newPage
  }

  return { loading, list, total, page, pageSize, loadData, handlePageChange }
}
