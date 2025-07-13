<script setup lang="ts">
import { useApi } from '@/common/api'
import { type Resource } from '@/common/defines'
const loading = ref(true)
const search = ref('')
const shops = ref<Resource[]>([])
const serverItems = ref<Resource[]>([])

const api = useApi()
const router = useRouter()

const shopId = ref('all')

const loadShops = async () => {
  const res = (await api.all('shop')).data
  const shopList: Resource[] = [
    { id: 'all', name: '[全て]' },
    { id: 'null', name: '[未指定]' }
  ]
  if (res.length) {
    shopList.push(...res)
  }
  shops.value = shopList
}

const loadItems = async () => {
  loading.value = true
  if (shops.value.length <= 2) await loadShops()
  serverItems.value = (await api.all('device')).data
  loading.value = false
}

const onRowClick = (e: PointerEvent, row: { index: number; item: Record<string, string | number> }) => router.push(`/device/${row.item.id}`)

loadItems()
</script>

<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4" flat>
      <span class="font-weight-bold me-auto">デバイス一覧</span>
    </v-card-title>

    <v-card-text>
      <v-data-table
        :headers="[
          {
            key: 'id',
            title: 'GPS端末ID',
            sortable: true
          },
          {
            key: 'name',
            title: 'GPS端末名',
            sortable: true
          },
          {
            key: 'company_id',
            title: 'ショップ',
            sortable: true,
            value: (item: Resource) => shops.find((s: Resource) => item.company_id == s.id)?.name || '[未指定]'
          },
          {
            key: 'battery',
            title: 'バッテリー',
            sortable: false,
            align: 'end'
          },
          {
            key: 'last_measure_at',
            title: '位置履歴同期日時',
            sortable: true
          },
          {
            key: 'status',
            title: 'ステータス',
            sortable: true
          }
        ]"
        :items="serverItems.filter((d) => shopId == 'all' || (shopId == 'null' && !d.company_id) || d.company_id == shopId)"
        :loading="loading"
        :search="search"
        hover
        style="min-height: 450px"
        @click:row="onRowClick"
      >
        <template #top>
          <v-sheet class="d-flex ma-1">
            <v-select
              v-if="shops.length"
              v-model="shopId"
              label="ショップ"
              :items="shops"
              class="ma-2"
              item-title="name"
              item-value="id"
              variant="outlined"
              hide-details
              max-width="250px"
            >
            </v-select>
            <v-text-field
              v-model="search"
              label="検索"
              class="ma-2"
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
        <template #[`item.battery`]="{ value }">
          <v-progress-linear
            :model-value="value"
            :color="value < 10 ? 'error' : value < 50 ? 'warning' : 'success'"
            bg-color="grey200"
            :height="25"
          >
            <strong :style="{ color: value < 10 ? 'red' : 'white' }">{{ value }}%</strong>
          </v-progress-linear>
        </template>
        <template #[`item.status`]="{ value }">
          <v-chip
            :color="value == 'active' ? 'success' : value == 'deleted' ? 'error' : 'warning'"
            :text="value"
            class="text-uppercase"
            size="small"
            label
          ></v-chip>
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>

<route lang="yaml">
meta:
  roles: [1, 9]
</route>
