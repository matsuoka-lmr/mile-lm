<template>
  <v-card>
    <v-card-text>
      <v-card-title class="d-flex align-center">
        <span class="me-auto">{{ title }}一覧</span>
        <v-btn icon to="create" variant="flat">
          <v-icon icon="mdi-plus"></v-icon>
          <v-tooltip activator="parent" location="bottom">新規{{ title }}作成</v-tooltip>
        </v-btn>
      </v-card-title>
      <v-container v-if="searchFields.length" class="list-bar">
        <v-text-field
          v-for="searchField in searchFields"
          :key="searchField.key"
          v-model="options.search[searchField.key]"
          prepend-inner-icon="mdi-magnify"
          class="ma-2"
          density="compact"
          hide-details
          variant="underlined"
          clearable
          :label="searchField.title"
          :placeholder="searchField.title"
          trim
        />
      </v-container>
      <v-divider></v-divider>
      <div v-if="loading" class="list-message">
        <v-progress-circular indeterminate></v-progress-circular>
      </div>
      <div v-else-if="!list.length" class="list-message">データがありません</div>
      <v-data-table-server
        v-else
        :headers="listFields"
        :items="list"
        :items-length="total"
        :items-per-page="options.perPage"
        :page="options.page"
        :sort-by="options.sortBy"
        :loading="loading"
        item-value="name"
        @update:options="onOptionsUpdated"
      >
        <template #item="{ item }">
          <tr class="list-row" @click="onRowClicked(item)">
            <td v-for="field in listFields" :key="field.key">
              {{ render(field, item) }}
            </td>
          </tr>
        </template>
      </v-data-table-server>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { debouncedWatch } from '@vueuse/core'
import { useApi } from '@/common/api'
import { useRouter } from 'vue-router'
import { type Data, isSimple, type Resource, type Field, type Sort } from '@/common/defines'

const props = defineProps<{
  resource: string
  title: string
  fields: Field[]
}>()

const listFields = props.fields.filter((f) => f.list != false)
const searchFields = listFields.filter((f) => f.search)
const empty = Object.fromEntries(new Map(searchFields.map((f) => [f.key, ''])))

const loading = ref(false)
const list = ref<Resource[]>([])
const total = ref(0)
const options = reactive<{ page: number; perPage: number; sortBy: Sort[]; search: { [key: string]: string } }>({
  page: 1,
  perPage: 10,
  sortBy: [],
  search: empty
})

const onOptionsUpdated = ({ page, itemsPerPage, sortBy }: { page: number; itemsPerPage: number; sortBy: Sort[] }) => {
  options.page = page
  options.perPage = itemsPerPage
  options.sortBy = sortBy
}

const api = useApi()
const load = () => {
  loading.value = true
  api.list(props.resource, options).then((result) => {
    list.value = result.data
    total.value = result.total
    options.page = result.page
    loading.value = false
  })
}
debouncedWatch(options, load, { deep: true, debounce: 300 })

const router = useRouter()
const onRowClicked = (item: Resource) => {
  if (item && item.id) router.push(`./${item.id}`)
}

const getColValue = (item: { [s: string]: Data }, key: string) => {
  const keys = key.split('.')
  for (let i = 0; i < keys.length - 1; i++) {
    const value = item[keys[i]]
    item = !value || isSimple(value) || Array.isArray(value) ? {} : value
  }
  const v = String(item[keys[keys.length - 1]]) || ''
  return v
}

const render = (field: Field, item: Resource) => {
  if (field.renderer) return field.renderer(item)
  const value = getColValue(item, field.key)
  if (Array.isArray(field.options)) {
    return field.options.find((op) => op && op.value == value)?.title || ''
  }
  return value
}

// const reset = () => {
//   options.search = empty
// }

// const del = (id) =>
//   api.del('user', id).then((ret) => {
//     if (ret.success) this.load(false)
//   })

load()
</script>

<style lang="scss" scoped>
.list-bar {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  padding: 0 0.5rem;
}
.list-message {
  text-align: center;
  align-items: center;
  padding: 4rem;
}
tr.list-row:hover {
  cursor: pointer;
  td {
    background-color: rgba(146, 186, 241, 0.702);
  }
}
</style>
