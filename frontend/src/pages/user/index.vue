<script setup lang="ts">
import { useApi } from '@/common/api'
import { type Resource } from '@/common/defines'
import { useAuthStore } from '@/stores'
const { user } = useAuthStore()
const loading = ref(true)
const search = ref('')
const companies = ref<Resource[]>(user?.company && [9, 19, 99].includes(user?.role || 0) ? [user?.company] : [])
const serverItems = ref<Resource[]>([])

const api = useApi()
const router = useRouter()

const loadItems = () => {
  loading.value = true
  const p = [1, 9, 99].includes(user?.role || 0)
    ? api.all('shop').then((resp) => {
        const { data } = resp
        companies.value = companies.value.concat(data)
      })
    : Promise.resolve
  return Promise.all([
    p,
    api.all('user').then((resp) => {
      const { data } = resp
      serverItems.value = data || []
    })
  ]).finally(() => {
    loading.value = false
  })
}

const onRowClick = (e: PointerEvent, row: { index: number; item: Record<string, string | number> }) => router.push(`/user/${row.item.id}`)
loadItems()
</script>

<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4" flat>
      <span class="font-weight-bold me-auto">ユーザー一覧</span>
      <v-btn to="user/create" prepend-icon="mdi-plus">
        新規作成
        <v-tooltip activator="parent" location="bottom">新規ユーザー作成</v-tooltip>
      </v-btn>
    </v-card-title>

    <v-card-text>
      <v-data-table
        :headers="[
          {
            title: '会社名',
            key: 'company_id',
            sortable: true,
            align: 'start',
            nowrap: true,
            value: (item) => companies.find((c) => c.id == item.company_id)?.name
          },
          {
            title: '権限',
            key: 'role',
            sortable: true,
            align: 'start',
            nowrap: true,
            value: (item) => (item.role == 99 ? 'システム管理者' : Number(item.role) % 10 == 9 ? '管理者' : '一般ユーザー')
          },
          {
            title: '名前',
            key: 'name',
            sortable: true,
            align: 'start',
            nowrap: true
          },
          {
            title: 'メールアドレス',
            key: 'email',
            sortable: true,
            align: 'start',
            nowrap: true
          },
          {
            title: '電話番号',
            key: 'phone',
            sortable: true,
            align: 'start',
            nowrap: true
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
  roles: [1, 9, 19]
</route>
