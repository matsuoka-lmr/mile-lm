<script setup lang="ts">
import { useApi } from '@/common/api'
import { type Resource } from '@/common/defines'
const loading = ref(true)
const search = ref('')
const serverItems = ref<Resource[]>([])

const api = useApi()
const router = useRouter()

const loadItems = () => {
  loading.value = true
  return api
    .all('shop')
    .then((resp) => {
      const { data } = resp
      serverItems.value = data || []
    })
    .finally(() => {
      loading.value = false
    })
}

const onRowClick = (e: PointerEvent, row: { index: number; item: Record<string, string | number> }) => router.push(`/shop/${row.item.id}`)
loadItems()
</script>

<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4" flat>
      <span class="font-weight-bold me-auto">タイヤショップ一覧</span>
      <v-btn to="shop/create" prepend-icon="mdi-plus">
        新規作成
        <v-tooltip activator="parent" location="bottom">新規タイヤショップ情報作成</v-tooltip>
      </v-btn>
    </v-card-title>

    <v-card-text>
      <v-data-table
        :headers="[
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

<route lang="yaml">
meta:
  roles: [1, 9]
</route>
