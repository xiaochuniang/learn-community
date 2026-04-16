<template>
  <div class="page-card">
    <div class="search-bar">
      <el-input v-model="query.keyword" placeholder="搜索标题/内容" clearable style="width:200px" @keyup.enter="loadData" />
      <el-select v-model="query.auditStatus" placeholder="审核状态" clearable style="width:130px">
        <el-option label="待审核" :value="0" /><el-option label="已通过" :value="1" /><el-option label="已拒绝" :value="2" />
      </el-select>
      <el-button type="primary" :icon="Search" @click="loadData">搜索</el-button>
      <el-button @click="resetQuery">重置</el-button>
    </div>
    <el-table v-loading="loading" :data="tableData" border stripe>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="title" label="标题" min-width="160" show-overflow-tooltip />
      <el-table-column prop="content" label="内容摘要" min-width="200" show-overflow-tooltip>
        <template #default="{ row }">{{ truncate(row.content, 40) }}</template>
      </el-table-column>
      <el-table-column prop="likeCount" label="点赞" width="70" />
      <el-table-column prop="commentCount" label="评论" width="70" />
      <el-table-column label="审核" width="90">
        <template #default="{ row }">
          <el-tag :type="['warning','success','danger'][row.auditStatus]" size="small">
            {{ ['待审','通过','拒绝'][row.auditStatus] }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="发布时间" width="160">
        <template #default="{ row }">{{ formatDateTime(row.createTime) }}</template>
      </el-table-column>
      <el-table-column label="操作" width="160" fixed="right">
        <template #default="{ row }">
          <template v-if="row.auditStatus === 0">
            <el-button size="small" type="success" link @click="doAudit(row, 1)">通过</el-button>
            <el-button size="small" type="danger" link @click="doAudit(row, 2)">拒绝</el-button>
          </template>
          <el-button size="small" type="danger" link @click="doDelete(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination v-model:current-page="query.page" v-model:page-size="query.pageSize" :total="total"
      :page-sizes="[10,20,50]" layout="total, sizes, prev, pager, next" class="pagination" @change="loadData" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import { getPostList, auditPost, deletePost } from '@/api/community'
import { formatDateTime, truncate } from '@/utils/format'

const loading = ref(false)
const tableData = ref<any[]>([])
const total = ref(0)
const query = reactive({ page: 1, pageSize: 10, keyword: '', auditStatus: undefined as number|undefined })

async function loadData() {
  loading.value = true
  try { const res = await getPostList(query); tableData.value = (res.data as any).list; total.value = (res.data as any).total }
  finally { loading.value = false }
}
function resetQuery() { query.keyword = ''; query.auditStatus = undefined; query.page = 1; loadData() }
async function doAudit(row: any, status: number) {
  await auditPost(row.id, { status, remark: '' }); ElMessage.success('操作成功'); loadData()
}
async function doDelete(row: any) {
  await ElMessageBox.confirm('确定删除该帖子？', '提示', { type: 'warning' })
  await deletePost(row.id); ElMessage.success('删除成功'); loadData()
}
onMounted(loadData)
</script>
<style scoped>
.search-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.pagination { margin-top: 16px; justify-content: flex-end; }
</style>
