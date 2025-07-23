<script setup lang="ts">
import { useApi } from '@/common/api'
import { type ID, type Resource } from '@/common/defines'
import { useAuthStore } from '@/stores'
const loading = ref(true)
const search = ref('')
const shops = ref<Resource[]>([{ id: null, name: '[未指定]' }])
const customers = ref<Resource[]>([{ id: null, name: '[未指定]' }])
const serverItems = ref<Resource[]>([])

const { user } = useAuthStore()

const api = useApi()
const router = useRouter()

const shopId = ref<ID | null>('')
const customerId = ref<ID | null>('')

const loadCustomers = async () => {
  loading.value = true
  if (customers.value.length == 1) {
    customers.value.push(...(await api.all('customer')).data)
    shops.value = shops.value.filter((s) => s.id == null || customers.value.find((c) => c.manage_company_id == s.id))
    shopId.value = shops.value.length ? (shops.value[0].id as ID) : null
  }
  customerId.value =
    shopId.value != null && customers.value.length > 1
      ? (customers.value.filter((c) => c.manage_company_id == shopId.value)[0].id as ID)
      : null
  serverItems.value = customerId.value
    ? (await api.all('vehicle', customerId.value ? { search: { company_id: String(customerId.value) } } : {})).data
    : []
  loading.value = false
}
const loadShops = async () => (shops.value = user?.role == 11 || user?.role == 19 ? [user.company] : (await api.all('shop')).data)

const loadItems = async () => {
  loading.value = true
  if (shops.value.length == 1) await loadShops()
  if (customers.value.length == 1) await loadCustomers()
  else {
    serverItems.value = (await api.all('vehicle', { search: { company_id: String(customerId.value) } })).data
    loading.value = false
  }
}

const onRowClick = (e: PointerEvent, row: { index: number; item: Record<string, string | number> }) =>
  router.push(`/vehicle/${row.item.id}`)
loadItems()
</script>

<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4" flat>
      <span class="font-weight-bold me-auto">車両一覧</span>
      <v-btn to="/vehicle/create" prepend-icon="mdi-plus">
        新規作成
        <v-tooltip activator="parent" location="bottom">新規車両情報作成</v-tooltip>
      </v-btn>
    </v-card-title>

    <v-card-text>
      <v-data-table
        :headers="[
          {
            key: 'model',
            title: '車種',
            sortable: true
          },
          {
            key: 'number',
            title: 'ナンバー',
            sortable: true
          },
          {
            key: 'device_id',
            title: 'GPS端末ID',
            sortable: true
          },
          {
            key: 'inspection_date',
            title: '車検日',
            sortable: true
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
            <v-select
              v-if="[1, 9, 99].includes(Number(user?.role))"
              v-model="shopId"
              label="ショップ"
              :items="shops"
              class="ma-2"
              item-title="name"
              item-value="id"
              variant="outlined"
              hide-details
              max-width="250px"
              @update:model-value="loadCustomers"
            >
            </v-select>
            <v-select
              v-if="shops.length > 1 && customers.length > 1"
              v-model="customerId"
              label="顧客"
              :items="customers.filter((c: Resource) => shopId == null || c.id == null || c.manage_company_id == shopId)"
              class="ma-2"
              item-title="name"
              item-value="id"
              variant="outlined"
              hide-details
              max-width="250px"
              @update:model-value="loadItems"
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
      </v-data-table>
    </v-card-text>
  </v-card>
</template>
