<template>
  <div class="page-card">
    <div class="search-bar">
      <el-input v-model="query.keyword" placeholder="订单号/用户ID" clearable style="width:200px" @keyup.enter="loadData" />
      <el-select v-model="query.orderStatus" placeholder="订单状态" clearable style="width:130px">
        <el-option label="待确认" :value="0" /><el-option label="已确认" :value="1" />
        <el-option label="服务中" :value="2" /><el-option label="已完成" :value="3" />
        <el-option label="已取消" :value="4" /><el-option label="已退款" :value="6" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="orderNo" label="订单号" width="180" show-overflow-tooltip />
      <el-table-column prop="userId" label="用户ID" width="90" />
      <el-table-column prop="tutorId" label="陪伴师ID" width="100" />
      <el-table-column prop="payAmount" label="实付(元)" width="100" />
      <el-table-column label="支付方式" width="100">
        <template #default="{ row }">{{ ['未支付','微信支付','余额支付'][row.payType] }}</template>
      </el-table-column>
      <el-table-column label="订单状态" width="100">
        <template #default="{ row }">
          <el-tag :type="['warning','','primary','success','info','danger','danger'][row.orderStatus]" size="small">
            {{ ['待确认','已确认','服务中','已完成','已取消','申请退款','已退款'][row.orderStatus] }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="下单时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
    </el-table>
    <el-pagination v-model:current-page="query.page" v-model:page-size="query.pageSize" :total="total"
      :page-sizes="[10,20,50]" layout="total, sizes, prev, pager, next" class="pagination" @change="loadData" />
  </div>
</template>
<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { Search } from '@element-plus/icons-vue'
import { getOrderList } from '@/api/order'
import { formatDateTime } from '@/utils/format'
const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, keyword: '', orderStatus: undefined as number|undefined })
async function loadData() {
  loading.value = true
  try { const res = await getOrderList(query); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
