<script setup lang="ts">
import { useApi } from '@/common/api'
import { type Resource } from '@/common/defines'
import { useAuthStore } from '@/stores'
const loading = ref(true)
const search = ref('')
const serverItems = ref<Resource[]>([])

const api = useApi()
const router = useRouter()
const { user } = useAuthStore()
const shops = ref<Resource[]>(user?.role == 9 || user?.role == 99 || !user?.company ? [] : [user.company])

const loadItems = async () => {
  loading.value = true
  if ((user?.role == 9 || user?.role == 99 || user?.role == 1) && shops.value.length == 0) {
    const { data } = await api.all('shop')
    shops.value = data
  }

  const resp = await api.all('customer')
  serverItems.value = resp.data || []
  loading.value = false

  // デバッグ用ログ
  console.log('shops:', shops.value);
  console.log('serverItems:', serverItems.value);
  serverItems.value.forEach(item => {
    const foundShop = shops.value.find(s => s.id === item.manage_company_id); // === で厳密な比較
    console.log(`Customer ID: ${item.id}, manage_company_id: ${item.manage_company_id}, Found Shop Name: ${foundShop?.name || 'Not Found'}`);
  });
}

const onRowClick = (e: PointerEvent, row: { index: number; item: Record<string, string | number> }) =>
  router.push(`/customer/${row.item.id}`)
loadItems()
</script>

<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4" flat>
      <span class="font-weight-bold me-auto">顧客一覧</span>
      <v-btn to="customer/create" prepend-icon="mdi-plus">
        新規作成
        <v-tooltip activator="parent" location="bottom">新規顧客情報作成</v-tooltip>
      </v-btn>
    </v-card-title>

    <v-card-text>
      <v-data-table
        :headers="[
          {
            title: 'ショップ',
            key: 'manage_company_id',
            sortable: true,
            align: 'start',
            nowrap: true,
            value: (item) => shops.find((s) => s.id == item.manage_company_id)?.name || ''
          },
          {
            title: '会社名',
            key: 'name',
            sortable: true,
            align: 'start',
            nowrap: true
          },
          {
            title: '電話',
            key: 'phone',
            sortable: true,
            align: 'start',
            nowrap: true
          },
          {
            title: '住所',
            key: 'address',
            sortable: true,
            align: 'start',
            nowrap: true,
            width: '50%'
          }
        ]"
        :items="serverItems"
        :loading="loading"
        :search="search"
        hover
        style="min-height: 450px"
        @click:row="onRowClick"
      >
        <template #top>
          <v-sheet class="d-flex ma-1">
            <v-text-field
              v-model="search"
              label="検索"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              hide-details
              single-line
              clearable
              density="compact"
              max-width="250px"
            ></v-text-field>
            <v-spacer></v-spacer>
            <v-btn prepend-icon="mdi-reload" @click.prevent="loadItems">
              リロード
              <v-tooltip activator="parent">最新の情報を再取得</v-tooltip>
            </v-btn>
          </v-sheet>
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>
